<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\Entity\User;
use App\Helper\FlashType;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Field\FormField;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Symfony\Component\String\u;

class SecurityControllerSendConfirmLinkTest extends WebTestCase
{
    use MailerAssertionsTrait;

    protected const URL_EN_CONFIRM_RESEND = '/micro-post/en/confirm_resend';
    protected const URL_EN_LOGIN = '/micro-post/en/login';
    protected const URL_CONFIRM_LOGIN_EMAIL_PATTERN = '/micro-post/%s/confirm/%s';

    /** @var \App\Repository\UserRepository */
    protected $userRepository;
    /** @var \Doctrine\Persistence\ObjectManager */
    private $em;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = self::getContainer()->get('doctrine')->getManager();
        $this->userRepository = $this->em->getRepository(User::class);
        $this->translator = self::getContainer()->get(TranslatorInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }

    public function testSendConfirmLinkSuccess(): void
    {
        // Prepare user for request - user must be deactivate.
        $user = $this->userRepository->findOneBy([]);
        $user->setIsActive(false)->setConfirmationToken(null);
        $this->em->persist($user);
        $this->em->flush();

        self::ensureKernelShutdown();
        $client = static::createClient();

        $crawler = $client->request('GET', self::URL_EN_CONFIRM_RESEND);
        self::assertResponseIsSuccessful();

        $form = $this->getForm($crawler);
        $data = $this->getDataForSubmit($form, $user);

        $client->submit($form, $data);

        self::assertResponseRedirects();
        self::assertEquals(self::URL_EN_LOGIN, $client->getResponse()->headers->get('location'));

        // test flash message
        $flashMessage = $this->getFlashBag(FlashType::SUCCESS);
        self::assertContains($this->translator->trans('confirm_token_resend.success'), $flashMessage);

        $userUpdated = $this->userRepository->findOneBy(['email' => $user->getEmail()]);
        self::assertFalse($userUpdated->getIsActive());
        self::assertNotEmpty($userUpdated->getConfirmationToken());

        $email = self::getMailerMessage();
        $emailAsString = $email->toString();
        $headerMailToForRegExp = '/To:.*' . preg_quote($user->getEmail()) . '/i';
        self::assertMatchesRegularExpression($headerMailToForRegExp, $emailAsString);

        $confirmLink = sprintf(
            self::URL_CONFIRM_LOGIN_EMAIL_PATTERN,
            $userUpdated->getPreferences()->getLocale(), $userUpdated->getConfirmationToken());
        self::assertEmailHtmlBodyContains($email, $confirmLink);
        self::assertEmailTextBodyContains($email, $confirmLink);
    }

    public function testSendConfirmLinkFail(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $crawler = $client->request('GET', self::URL_EN_CONFIRM_RESEND);
        self::assertResponseIsSuccessful();;

        $user = (new User())->setEmail('wrong@email.domain.ca');

        $form = $this->getForm($crawler);
        $data = $this->getDataForSubmit($form, $user);

        $crawler = $client->submit($form, $data);
        self::assertResponseIsUnprocessable();
        // test flash message on page
        $message = $crawler->filter('main .alert.alert-danger div')->first()->text();
        self::assertEquals($this->translator->trans('confirm_token_resend.fail'), $message);
    }

    /**
     * @return string[]
     */
    protected function getFlashBag(string $flashType): array
    {
        return self::getContainer()->get(SessionInterface::class)->getFlashBag()->get($flashType) ?? [];
    }

    protected function getForm(Crawler $crawler): Form
    {
        return $crawler->filter('form button[name$="[send]"]')->form();
    }

    protected function getDataForSubmit(Form $form, User $user): array
    {
        return array_reduce($form->all(), static function (array $acc, FormField $item) use ($user) {
            if (u($item->getName())->endsWith('[email]')) {
                $acc[$item->getName()] = $user->getEmail();
            }
            return $acc;
        }, []);
    }
}
