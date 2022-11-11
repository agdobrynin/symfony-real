<?php
declare(strict_types=1);

namespace App\Tests\Funtional\MicroPost\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class ProfileControllerTest extends WebTestCase
{
    protected const URL_PROFILE_VIEW = '/micro-post/en/profile/view';
    protected const URL_PROFILE_EDIT = '/micro-post/en/profile/edit';

    public function testProfileIsAnonymous(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('GET', self::URL_PROFILE_VIEW);
        self::assertResponseRedirects();

        $crawlerLoginPage = $client->followRedirect();

        $this->assertCount(1, $crawlerLoginPage->filter('input[name$="[_username]"]'));
        $this->assertCount(1, $crawlerLoginPage->filter('input[name$="[_password]"]'));
    }

    public function testProfileAuthUserFormData(): void
    {
        $user = $this->getAuthUser('admin');

        self::ensureKernelShutdown();
        $client = self::createClient();
        $client->loginUser($user);

        $crawler = $client->request('GET', self::URL_PROFILE_EDIT);

        self::assertResponseIsSuccessful();
        $profileElements = $this->getProfileEditElements($crawler);
        self::assertEquals($profileElements->emoji->attr("value"), $user->getEmoji());
        self::assertEquals($profileElements->email->attr("value"), $user->getEmail());
        self::assertEquals($profileElements->userLocaleSelectedValue->attr("value"), $user->getPreferences()->getLocale());
    }

    public function testProfileAuthUserEditDataWithoutEmail(): void
    {
        $user = $this->getAuthUser('admin');

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

        $updatedUser = $this->getAuthUser('admin');
        self::assertEquals($newEmoji, $updatedUser->getEmoji());
        self::assertEquals($newLocale, $updatedUser->getPreferences()->getLocale());
    }

    public function testProfileAuthUserEditDataEmailOnly(): void
    {
        $user = $this->getAuthUser('admin');
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

        $updatedUser = $this->getAuthUser('admin');
        self::assertEquals($newEmail, $updatedUser->getEmail());
        self::assertFalse($updatedUser->getIsActive());
        self::assertNotNull($updatedUser->getConfirmationToken());

        $crawlerLoginPage = $client->followRedirect();
        // redirect to login page.
        $this->assertCount(1, $crawlerLoginPage->filter('input[name$="[_username]"]'));
        $this->assertCount(1, $crawlerLoginPage->filter('input[name$="[_password]"]'));
    }

    protected function getProfileEditElements(Crawler $crawler): object
    {
        $dto = new class {
            public $emoji;
            public $email;
            public $userLocale;
            public $userLocaleSelectedValue;
        };

        $dto->emoji = $crawler->filter('input[name$="[emoji]"]')->first();
        $dto->email = $crawler->filter('input[name$="[email]"]')->first();
        $dto->userLocale = $crawler->filter('select[name$="[userLocale]"]')->first();
        $dto->userLocaleSelectedValue = $dto->userLocale->children('option')
            ->reduce(function (Crawler $option) {
                return $option->attr("selected");
            });

        return $dto;
    }

    protected function getAuthUser(string $login): User
    {
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        // ⚠ user admin defined in App\DataFixtures\AppFixtures
        return $userRepository->findOneBy(['login' => $login]);
    }
}
