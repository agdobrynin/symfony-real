<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\User;
use App\Tests\Functional\MicroPost\Controller\Utils\ProfileEditElementDto;
use App\Tests\Functional\MicroPost\Controller\Utils\ProfilePasswordElementDto;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class ProfileControllerTest extends WebTestCase
{
    use MailerAssertionsTrait;

    protected const URL_PROFILE_VIEW = '/micro-post/en/profile/view';
    protected const URL_PROFILE_EDIT = '/micro-post/en/profile/edit';
    protected const URL_PROFILE_PASSWORD = '/micro-post/en/profile/password';
    protected const URL_CONFIRM_LOGIN_EMAIL_PATTERN = '/micro-post/%s/confirm/%s';

    /** @var \App\Repository\UserRepository */
    protected $userRepository;
    /**
     * @var \Doctrine\Persistence\ObjectManager
     */
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

    public function testProfileIsAnonymous(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('GET', self::URL_PROFILE_VIEW);
        self::assertResponseRedirects();

        $this->checkRedirectToLoginPage($client->followRedirect());
    }

    public function testProfileAuthUserFormData(): void
    {
        $adminDto = AppFixtures::searchUserFixtureByProperty('isAdmin', true);
        $user = $this->userRepository->findOneBy(['login' => $adminDto->login]);

        self::ensureKernelShutdown();
        $client = self::createClient();
        $client->loginUser($user);

        $crawler = $client->request('GET', self::URL_PROFILE_EDIT);

        self::assertResponseIsSuccessful();
        $profileElements = $this->getProfileEditElements($crawler);
        self::assertEquals($profileElements->emoji->attr("value"), $user->getEmoji());
        self::assertEquals($profileElements->email->attr("value"), $user->getEmail());
        self::assertEquals($profileElements->userLocaleSelected->attr("value"), $user->getPreferences()->getLocale());
    }

    public function testProfileAuthUserEditDataWithoutEmail(): void
    {
        $adminDto = AppFixtures::searchUserFixtureByProperty('isAdmin', true);
        $user = $this->userRepository->findOneBy(['login' => $adminDto->login]);

        self::ensureKernelShutdown();
        $client = self::createClient();
        $client->loginUser($user);

        $crawler = $client->request('GET', self::URL_PROFILE_EDIT);
        $profileElements = $this->getProfileEditElements($crawler);

        $newEmoji = '😛';
        $newLocale = 'en';
        $newData[$profileElements->emoji->attr("name")] = $newEmoji;
        $newData[$profileElements->userLocale->attr("name")] = $newLocale;
        $button = $crawler->filter('button[type="submit"][name$="[save]"]');
        $client->submitForm($button->attr("name"), $newData);
        self::assertResponseRedirects();
        $client->followRedirect();

        $adminDto = AppFixtures::searchUserFixtureByProperty('isAdmin', true);
        $user = $this->userRepository->findOneBy(['login' => $adminDto->login]);
        self::assertEquals($newEmoji, $user->getEmoji());
        self::assertEquals($newLocale, $user->getPreferences()->getLocale());
    }

    public function testProfileAuthUserEditDataEmailOnly(): void
    {
        $adminDto = AppFixtures::searchUserFixtureByProperty('isAdmin', true);
        $user = $this->userRepository->findOneBy(['login' => $adminDto->login]);
        self::assertTrue($user->getIsActive());
        self::assertNull($user->getConfirmationToken());

        self::ensureKernelShutdown();
        $client = self::createClient();
        $client->loginUser($user);

        $crawler = $client->request('GET', self::URL_PROFILE_EDIT);
        $profileElements = $this->getProfileEditElements($crawler);

        $newEmail = 'super@email.domain.dev';
        $newData[$profileElements->email->attr("name")] = $newEmail;
        $button = $crawler->filter('button[type="submit"][name$="[save]"]');
        $client->submitForm($button->attr("name"), $newData);
        self::assertResponseRedirects();

        // Before change email address user deactivate, set confirmation token, send email with confirmation link
        $user = $this->userRepository->findOneBy(['login' => 'admin']);
        self::assertEquals($newEmail, $user->getEmail());
        $this->deactivateUserAndSendEmailWithConfirmToken($user);

        $this->checkRedirectToLoginPage($client->followRedirect());
    }

    public function testProfileAuthUserChangePasswordCurrentPasswordFail(): void
    {
        $adminDto = AppFixtures::searchUserFixtureByProperty('isAdmin', true);
        $user = $this->userRepository->findOneBy(['login' => $adminDto->login]);

        self::ensureKernelShutdown();
        $client = self::createClient();
        $client->loginUser($user);

        $crawler = $client->request('GET', self::URL_PROFILE_PASSWORD);

        $dto = $this->getProfilePasswordFields($crawler);

        $data = [
            $dto->currentPassword->attr('name') => 'wrong-current-password',
            $dto->newPassword->attr('name') => 'password',
            $dto->newPasswordRetype->attr('name') => 'password',
        ];

        $crawler = $client->submit($dto->form, $data);
        self::assertResponseIsUnprocessable();
        $dto = $this->getProfilePasswordFields($crawler);
        $this->checkInvalidCssClassForField($dto->currentPassword);
    }

    public function testProfileAuthUserChangePasswordNewPasswordIsShortFail(): void
    {
        $adminDto = AppFixtures::searchUserFixtureByProperty('isAdmin', true);
        $user = $this->userRepository->findOneBy(['login' => $adminDto->login]);

        self::ensureKernelShutdown();
        $client = self::createClient();
        $client->loginUser($user);

        $crawler = $client->request('GET', self::URL_PROFILE_PASSWORD);

        $dto = $this->getProfilePasswordFields($crawler);

        $data = [
            // password defined in App\DataFixtures\AppFixtures for user admin
            $dto->currentPassword->attr('name') => 'qwerty',
            $dto->newPassword->attr('name') => '1',
            $dto->newPasswordRetype->attr('name') => '1',
        ];

        $crawler = $client->submit($dto->form, $data);

        self::assertResponseIsUnprocessable();
        $dto = $this->getProfilePasswordFields($crawler);
        $this->checkInvalidCssClassForField($dto->newPassword);
    }

    public function testProfileAuthUserChangePasswordNewPasswordNotMatchFail(): void
    {
        $adminDto = AppFixtures::searchUserFixtureByProperty('isAdmin', true);
        $user = $this->userRepository->findOneBy(['login' => $adminDto->login]);

        self::ensureKernelShutdown();
        $client = self::createClient();
        $client->loginUser($user);

        $crawler = $client->request('GET', self::URL_PROFILE_PASSWORD);

        $dto = $this->getProfilePasswordFields($crawler);

        $data = [
            // password defined in App\DataFixtures\AppFixtures for user admin
            $dto->currentPassword->attr('name') => 'qwerty',
            $dto->newPassword->attr('name') => '123456',
            $dto->newPasswordRetype->attr('name') => '654321',
        ];

        $crawler = $client->submit($dto->form, $data);

        self::assertResponseIsUnprocessable();
        $dto = $this->getProfilePasswordFields($crawler);
        $this->checkInvalidCssClassForField($dto->newPassword);
    }

    public function testProfileAuthUserChangePasswordNewPasswordSuccess(): void
    {
        $adminDto = AppFixtures::searchUserFixtureByProperty('isAdmin', true);
        $user = $this->userRepository->findOneBy(['login' => $adminDto->login]);

        self::ensureKernelShutdown();
        $client = self::createClient();
        $client->loginUser($user);

        $crawler = $client->request('GET', self::URL_PROFILE_PASSWORD);

        $dto = $this->getProfilePasswordFields($crawler);

        $data = [
            // password defined in App\DataFixtures\AppFixtures for user admin
            $dto->currentPassword->attr('name') => 'qwerty',
            $dto->newPassword->attr('name') => 'ytrewq',
            $dto->newPasswordRetype->attr('name') => 'ytrewq',
        ];

        $client->submit($dto->form, $data);
        self::assertResponseRedirects();

        // Before change password the user deactivate, set confirmation token, send email with confirmation link
        $user = $this->userRepository->findOneBy(['login' => 'admin']);
        $this->deactivateUserAndSendEmailWithConfirmToken($user);
        $this->checkRedirectToLoginPage($client->followRedirect());
    }

    protected function checkRedirectToLoginPage($crawlerLoginPage): void
    {
        // redirect to login page.
        $this->assertCount(1, $crawlerLoginPage->filter('input[name$="[_username]"]'));
        $this->assertCount(1, $crawlerLoginPage->filter('input[name$="[_password]"]'));
    }

    protected function checkInvalidCssClassForField(Crawler $crawlerField): void
    {
        $cssClassInput = $crawlerField->attr('class');
        self::assertStringContainsString('is-invalid', $cssClassInput);
        $cssClassErrorHint = $crawlerField->nextAll()->first()->attr('class');
        self::assertStringContainsString('invalid-feedback', $cssClassErrorHint);
    }

    protected function deactivateUserAndSendEmailWithConfirmToken(User $user): void
    {
        self::assertFalse($user->getIsActive());
        self::assertNotNull($user->getConfirmationToken());

        $email = self::getMailerMessage();
        $confirmLink = sprintf(
            self::URL_CONFIRM_LOGIN_EMAIL_PATTERN,
            $user->getPreferences()->getLocale(), $user->getConfirmationToken());
        self::assertEmailHtmlBodyContains($email, $confirmLink);
        self::assertEmailTextBodyContains($email, $confirmLink);

        $emailAsString = $email->toString();
        $headerMailToForRegExp = '/To:.*' . preg_quote($user->getEmail()) . '/i';
        self::assertMatchesRegularExpression($headerMailToForRegExp, $emailAsString);

        $adminEmail = self::getContainer()->getParameter('micropost.admin.email');
        $headerMailFromForRegExp = '/From:.*' . preg_quote($adminEmail) . '/i';
        self::assertMatchesRegularExpression($headerMailFromForRegExp, $emailAsString);
    }

    protected function getProfileEditElements(Crawler $crawler): ProfileEditElementDto
    {
        $dto = new ProfileEditElementDto();
        $dto->emoji = $crawler->filter('input[name$="[emoji]"]')->first();
        $dto->email = $crawler->filter('input[name$="[email]"]')->first();
        $dto->userLocale = $crawler->filter('select[name$="[userLocale]"]')->first();
        $dto->userLocaleSelected = $dto->userLocale->filter('option[selected]');

        return $dto;
    }

    protected function getProfilePasswordFields(Crawler $crawler): ProfilePasswordElementDto
    {
        $dto = new ProfilePasswordElementDto();

        $dto->form = $crawler->filter('button[type="submit"][name$="[save]"]')->form();
        $dto->currentPassword = $crawler->filter('input[type="password"][name$="[password]"]')->first();
        $dto->newPassword = $crawler->filter('input[type="password"][name$="[password_new][first]"]')->first();
        $dto->newPasswordRetype = $crawler->filter('input[type="password"][name$="[password_new][second]"]')->first();

        return $dto;
    }
}
