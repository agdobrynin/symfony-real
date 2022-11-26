<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\Entity\User;
use App\Helper\FlashType;
use App\Tests\Functional\MicroPost\Controller\Utils\Helper;
use App\Tests\Functional\MicroPost\Controller\Utils\UserRandom;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Symfony\Component\String\u;

class RestorePasswordControllerConfirmedTest extends WebTestCase
{
    protected const URL_EN_CHANGE_PASSWORD_PATTERN = '/micro-post/en/restore/password/confirm/%s';

    /** @var EntityManagerInterface */
    private $em;
    /** @var \App\Repository\UserRepository */
    protected $userRepository;
    /** @var TranslatorInterface */
    private $translator;
    /** @var UserPasswordHasherInterface */
    private $hasher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->userRepository = $this->em->getRepository(User::class);
        $this->translator = self::getContainer()->get(TranslatorInterface::class);
        $this->hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }


    public function getSourceDataFormConfirmed(): \Generator
    {
        $successToken = Helper::randString(40);

        $user = UserRandom::minimal(static function (User $u) use ($successToken): User {
            $em = self::getContainer()->get(EntityManagerInterface::class);
            $u->setChangePasswordToken($successToken);
            $em->persist($u);
            $em->flush();

            return $u;
        });

        $login = $user->getLogin();

        yield 'fail token' => ['fail-token', $login, true, 'password', 'password', false];
        yield 'fail form' => [$successToken, $login, false, 'password1', 'password2', true];
        yield 'success token' => [$successToken, $login, false, 'password', 'password', false];
    }

    /**
     * @dataProvider getSourceDataFormConfirmed
     */
    public function testFormConfirmed(
        string $changePasswordToken,
        string $login,
        bool   $isFailToken,
        string $passwordFirst,
        string $passwordSecond,
        bool   $isFailForm
    ): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();

        $url = sprintf(self::URL_EN_CHANGE_PASSWORD_PATTERN, $changePasswordToken);

        $crawler = $client->request('GET', $url);

        if ($isFailToken) {
            self::assertResponseRedirects();
            $failFlash = self::getContainer()->get(SessionInterface::class)
                ->getFlashBag()->get(FlashType::DANGER);

            $failMessage = $this->translator->trans('restore_password.fail', [], null, 'en');
            self::assertTrue(\in_array($failMessage, $failFlash));

            return;
        }

        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form button[name$="[update]"]')->form();
        self::assertInstanceOf(Form::class, $form);

        foreach (array_keys($form->all()) as $fieldName) {
            $field = u($fieldName);

            if ($field->endsWith('[password][first]')) {
                $form->setValues([$fieldName => $passwordFirst]);
            } elseif ($field->endsWith('[password][second]')) {
                $form->setValues([$fieldName => $passwordSecond]);
            }
        }

        $user = $this->userRepository->findOneBy(['login' => $login]);
        self::assertEquals($changePasswordToken, $user->getChangePasswordToken());

        $client->submit($form);

        if ($isFailForm) {
            self::assertResponseIsUnprocessable();
        } else {
            self::assertResponseRedirects();

            $successFlash = self::getContainer()->get(SessionInterface::class)
                ->getFlashBag()->get(FlashType::SUCCESS);
            $message = $this->translator->trans('restore_password.password_changed');

            self::assertTrue(\in_array($message, $successFlash));

            $this->em->refresh($user);
            self::assertTrue($this->hasher->isPasswordValid($user, $passwordFirst));
        }
    }
}
