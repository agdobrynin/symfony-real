<?php
declare(strict_types=1);

namespace App\Tests\Integration\Service\MicroPost\User;

use App\Entity\User;
use App\Service\MicroPost\User\GetFollowersFollowingOfUserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GetFollowersFollowingOfUserServiceTest extends KernelTestCase
{
    /**
     * @var \Doctrine\Persistence\ObjectManager
     */
    private $em;
    /**
     * @var \App\Repository\UserRepository
     */
    private $userRepository;

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

    public function testGetFollowers(): void
    {
        /** @var GetFollowersFollowingOfUserServiceInterface $srv */
        $srv = self::getContainer()->get(GetFollowersFollowingOfUserServiceInterface::class);
        $user = $this->userRepository->findOneBy([]);

        $firstOfFollowers = self::getContainer()->getParameter('micropost.first.of.followers');
        $dtoFollowers = $srv->getDtoFollowers($user);
        self::assertCount($dtoFollowers->total, $user->getFollowers());
        self::assertLessThanOrEqual($firstOfFollowers, count($dtoFollowers->collection));

        if ($user->getFollowers()->count() > $firstOfFollowers) {
            self::assertEquals(($dtoFollowers->total - $firstOfFollowers), $dtoFollowers->remainder);
        } else {
            self::assertEquals(0, $dtoFollowers->remainder);
        }
    }

    public function testFollowings(): void
    {
        /** @var GetFollowersFollowingOfUserServiceInterface $srv */
        $srv = self::getContainer()->get(GetFollowersFollowingOfUserServiceInterface::class);
        $user = $this->userRepository->findOneBy([]);

        $firstOfFollowings = self::getContainer()->getParameter('micropost.first.of.followings');
        $dtoFollowing = $srv->getDtoFollowings($user);
        self::assertCount($dtoFollowing->total, $user->getFollowing());

        self::assertLessThanOrEqual($firstOfFollowings, count($dtoFollowing->collection));

        if ($user->getFollowing()->count() > $firstOfFollowings) {
            self::assertEquals(($dtoFollowing->total - $firstOfFollowings), $dtoFollowing->remainder);
        } else {
            self::assertEquals(0, $dtoFollowing->remainder);
        }
    }
}
