<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\Entity\User;
use App\Helper\FlashType;
use App\Tests\Functional\MicroPost\Controller\Utils\UserRandom;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Symfony\Component\String\u;

class RestorePasswordControllerFormRestoreTest extends WebTestCase
{
    use MailerAssertionsTrait;

    protected const URL_EN_RESTORE_PASSWORD = '/micro-post/en/restore/password/form';
    protected const URL_CONFIRMED_CHANGE_PASSWORD_PATTERN = '/micro-post/%s/restore/password/confirm/%s';

    /** @var \App\Repository\UserRepository */
    protected $userRepository;
    /** @var EntityManagerInterface */
    private $em;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->userRepository = $this->em->getRepository(User::class);
        $this->translator = self::getContainer()->get(TranslatorInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }

    public function getSourceDataFormRestore(): \Generator
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $user = UserRandom::minimal();

        $em->persist($user);
        $em->flush();
        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneBy([]);

        yield 'Success: user exist' => [$user->getEmail(), true, true];
        yield 'Fail: user not exist' => ['fail@domain.com', true, false];
        yield 'Fail: email is empty' => ['', false, false];
        yield 'Fail: email wrong' => ['abc-email', false, false];
    }

    /**
     * @dataProvider getSourceDataFormRestore
     */
    public function testFormRestore(string $email, bool $isValidForm, bool $notifyByEmail): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();

        $crawler = $client->request('GET', self::URL_EN_RESTORE_PASSWORD);
        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form button[name$="[send]"]')->form();
        self::assertInstanceOf(Form::class, $form);

        foreach (array_keys($form->all()) as $fieldName) {
            if (u($fieldName)->endsWith('[email]')) {
                $form->setValues([$fieldName => $email]);
            }
        }

        $client->submit($form);

        if (!$isValidForm) {
            self::assertResponseIsUnprocessable();
            $email = self::getMailerMessage();
            self::assertNull($email);
        } else {
            self::assertResponseRedirects();

            $successFlash = self::getContainer()->get(SessionInterface::class)
                ->getFlashBag()->get(FlashType::SUCCESS);

            $successMessage = $this->translator->trans('restore_password.success', [], null, 'en');
            self::assertTrue(\in_array($successMessage, $successFlash));

            $emailMessage = self::getMailerMessage();

            if ($notifyByEmail) {
                $user = $this->userRepository->findOneBy(['email' => $email]);
                $confirmLink = sprintf(self::URL_CONFIRMED_CHANGE_PASSWORD_PATTERN, $user->getPreferences()->getLocale(), $user->getChangePasswordToken());

                self::assertEmailHtmlBodyContains($emailMessage, $confirmLink);
                self::assertEmailTextBodyContains($emailMessage, $confirmLink);
            } else {
                self::assertNull($emailMessage);
            }
        }
    }
}
