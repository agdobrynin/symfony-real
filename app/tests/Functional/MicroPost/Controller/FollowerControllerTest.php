<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FollowerControllerTest extends WebTestCase
{
    protected const URL_FOLLOW_EN_PATTERN = '/micro-post/en/follow/%s';
    protected const URL_UNFOLLOW_EN_PATTERN = '/micro-post/en/unfollow/%s';
    protected const URL_LOGIN_EN = '/micro-post/en/login';

    /**
     * ⚠ user defined in App\DataFixtures\AppFixtures
     *
     * @var UserRepository
     */
    protected $userRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = self::getContainer()->get(UserRepository::class);
    }

    public function testFollowUserNotAuthUser(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $userFollower = $this->userRepository->findOneBy(['login' => 'blogger']);

        $url = sprintf(self::URL_FOLLOW_EN_PATTERN, $userFollower->getUuid());
        $client->request('GET', $url);
        self::assertResponseRedirects();
        $crawler = $client->followRedirect();
        self::assertStringEndsWith(self::URL_LOGIN_EN, $crawler->getUri());
    }

    public function testFollowUserAuthUser(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        // ⚠ all users defined in \App\DataFixtures\AppFixtures
        $userFollower = $this->userRepository->findOneBy(['login' => 'blogger']);

        $userFollow = $this->userRepository->findOneBy(['login' => 'admin']);
        $userFollow->getFollowers()->clear();
        $this->userRepository->add($userFollow, true);

        self::assertEquals(0, $userFollow->getFollowers()->count());

        $client->loginUser($userFollower);
        $url = sprintf(self::URL_FOLLOW_EN_PATTERN, $userFollow->getUuid());
        $client->request('GET', $url);

        self::assertResponseRedirects();
        $client->followRedirect();

        $userFollow = $this->userRepository->findOneBy(['login' => 'admin']);
        self::assertEquals(1, $userFollow->getFollowing()->count());
    }

    public function testUnfollowUserAuthUser(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        // ⚠ all users defined in App\DataFixtures\AppFixtures
        $bloggerUser = $this->userRepository->findOneBy(['login' => 'blogger']);
        $adminUser = $this->userRepository->findOneBy(['login' => 'admin']);

        if (!$bloggerUser->getFollowing()->contains($adminUser)) {
            $bloggerUser->follow($adminUser);
            $this->userRepository->add($bloggerUser, true);
        }

        $bloggerUser = $this->userRepository->findOneBy(['login' => 'blogger']);
        self::assertTrue($bloggerUser->getFollowing()->contains($adminUser));

        $client->loginUser($bloggerUser);
        $url = sprintf(self::URL_UNFOLLOW_EN_PATTERN, $adminUser->getUuid());
        $client->request('GET', $url);

        self::assertResponseRedirects();
        $client->followRedirect();

        $adminUser = $this->userRepository->findOneBy(['login' => 'admin']);
        $bloggerUser = $this->userRepository->findOneBy(['login' => 'blogger']);
        self::assertFalse($adminUser->getFollowers()->contains($bloggerUser));
    }
}
