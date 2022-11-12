<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    /** @var EntityManagerInterface */
    protected $em;

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
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

        $this->em->refresh($userFollow);
        self::assertTrue($userFollow->getFollowers()->contains($userFollower));
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

        $this->em->refresh($adminUser);
        $this->em->refresh($bloggerUser);
        self::assertFalse($adminUser->getFollowers()->contains($bloggerUser));
    }
}
