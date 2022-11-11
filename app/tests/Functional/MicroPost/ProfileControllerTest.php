<?php
declare(strict_types=1);

namespace App\Tests\Funtional\MicroPost\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileControllerTest extends WebTestCase
{
    protected const URL_PROFILE_VIEW = '/micro-post/en/profile/view';

    public function testProfileViewIsAnonymous(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('GET', self::URL_PROFILE_VIEW);
        self::assertResponseRedirects();

        $crawlerLoginPage = $client->followRedirect();
        $this->assertCount(1, $crawlerLoginPage->filter('input[name$="[_username]"]'));
        $this->assertCount(1, $crawlerLoginPage->filter('input[name$="[_password]"]'));
    }

    public function testProfileViewAuthUser(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        // ⚠ user admin defined in App\DataFixtures\AppFixtures
        $user = $userRepository->findOneBy(['login' => 'admin']);
        self::assertInstanceOf(User::class, $user);

        self::ensureKernelShutdown();
        $client = self::createClient();
        $client->loginUser($user);
        $crawler = $client->request('GET', self::URL_PROFILE_VIEW);

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('input[value="' . $user->getEmail() . '"]'));
        self::assertCount(1, $crawler->filter('input[value="' . $user->getEmoji() . '@' . $user->getNick() . '"]'));
    }
}
