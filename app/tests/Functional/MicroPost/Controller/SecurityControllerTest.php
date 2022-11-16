<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\Entity\User;
use App\Security\ConfirmationTokenGenerator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityControllerTest extends WebTestCase
{
    protected const URL_SUCCESS_AUTH_EN_LOCALE = '/micro-post/en/success_auth';
    protected const URL_SUCCESS_AUTH_REDIRECT_RU_LOCALE_PATTERN = '/micro-post/%s/';
    protected const URL_CONFIRM_EN_LOCALE_PATTERN = '/micro-post/en/confirm/%s';
    protected const URL_LOGIN_EN_LOCALE = '/micro-post/en/login';

    /** @var \App\Repository\UserRepository */
    protected $userRepository;
    /** @var \Doctrine\Persistence\ObjectManager */
    private $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = self::getContainer()->get('doctrine')->getManager();
        $this->userRepository = $this->em->getRepository(User::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }

    public function testSuccessAuthFail(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('GET', self::URL_SUCCESS_AUTH_EN_LOCALE);
        self::assertResponseRedirects();

        $crawlerLoginPage = $client->followRedirect();
        // redirect to login page.
        $this->assertCount(1, $crawlerLoginPage->filter('input[name$="[_username]"]'));
        $this->assertCount(1, $crawlerLoginPage->filter('input[name$="[_password]"]'));
    }

    public function testSuccessAuthSuccess(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['login' => 'admin']);
        $newUserLocale = 'ru';
        $user->getPreferences()->setLocale($newUserLocale);
        $this->em->persist($user);
        $this->em->flush();

        $client->loginUser($user);

        $client->request('GET', self::URL_SUCCESS_AUTH_EN_LOCALE);
        self::assertResponseRedirects();
        $crawler = $client->followRedirect();
        // user has locale as 'ru' new url must be contain locale 'ru'.
        $newUrl = sprintf(self::URL_SUCCESS_AUTH_REDIRECT_RU_LOCALE_PATTERN, $newUserLocale);
        self::assertStringEndsWith($newUrl, $crawler->getUri());
    }

    public function testConfirmTokenSuccess(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['login' => 'admin']);
        $user->setIsActive(false);
        $confirmToken = (new ConfirmationTokenGenerator())->getRandomSecureToken();
        $user->setConfirmationToken($confirmToken);
        $this->em->persist($user);
        $this->em->flush();

        // Before update user - set is not active, add confirmation token.
        $this->em->refresh($user);
        self::assertNotNull($user->getConfirmationToken());
        self::assertFalse($user->getIsActive());

        $urlConfirm = sprintf(self::URL_CONFIRM_EN_LOCALE_PATTERN, $user->getConfirmationToken());
        $crawler = $client->request('GET', $urlConfirm);
        self::assertResponseIsSuccessful();

        // Before go to route update user - set is active, add remove confirmation token.
        $this->em->refresh($user);
        self::assertNull($user->getConfirmationToken());
        self::assertTrue($user->getIsActive());
        $successMessage = self::getContainer()
            ->get(TranslatorInterface::class)
            ->trans('confirmation_login.success', ['%login_link%' => self::URL_LOGIN_EN_LOCALE], null, 'en');

        self::assertStringContainsString($successMessage, $crawler->html());
    }

    public function testConfirmTokenFail(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        // undefined in database confirm token
        $urlConfirm = sprintf(self::URL_CONFIRM_EN_LOCALE_PATTERN, 'abc-abc-abc');
        $crawler = $client->request('GET', $urlConfirm);
        self::assertResponseIsUnprocessable();
        $wrongMessage = self::getContainer()->get(TranslatorInterface::class)
            ->trans('confirmation_login.wrong', [], null, 'en');
        self::assertStringContainsString($wrongMessage, $crawler->html());
    }
}
