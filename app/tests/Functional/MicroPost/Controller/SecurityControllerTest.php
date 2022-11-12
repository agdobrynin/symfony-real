<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\ConfirmationTokenGenerator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityControllerTest extends WebTestCase
{
    protected const URL_SUCCESS_AUTH_EN_LOCALE = '/micro-post/en/success_auth';
    protected const URL_SUCCESS_AUTH_REDIRECT_RU_LOCALE_PATTERN = '/micro-post/%s/';
    protected const URL_CONFIRM_EN_LOCALE_PATTERN = '/micro-post/en/confirm/%s';
    protected const URL_LOGIN_EN_LOCALE = '/micro-post/en/login';

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
        $user = self::getContainer()->get(UserRepository::class)->findOneBy(['login' => 'admin']);
        $newUserLocale = 'ru';
        $user->getPreferences()->setLocale($newUserLocale);
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
        /** @var UserRepository $repo */
        $repo = self::getContainer()->get(UserRepository::class);
        /** @var User $user */
        $user = $repo->findOneBy(['login' => 'admin']);
        $user->setIsActive(false);
        $confirmToken = (new ConfirmationTokenGenerator())->getRandomSecureToken();
        $user->setConfirmationToken($confirmToken);
        $repo->add($user, true);

        // Before update user - set is not active, add confirmation token.
        $userUpdated = $repo->findOneBy(['login' => 'admin']);
        self::assertNotNull($userUpdated->getConfirmationToken());
        self::assertFalse($userUpdated->getIsActive());

        $urlConfirm = sprintf(self::URL_CONFIRM_EN_LOCALE_PATTERN, $user->getConfirmationToken());
        $crawler = $client->request('GET', $urlConfirm);
        self::assertResponseIsSuccessful();

        // Before go to route update user - set is active, add remove confirmation token.
        $userUpdated = $repo->findOneBy(['login' => 'admin']);
        self::assertNull($userUpdated->getConfirmationToken());
        self::assertTrue($userUpdated->getIsActive());
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
