<?php
declare(strict_types=1);

namespace App\Tests\Unit\Service\MicroPost\User;

use App\Entity\User;
use App\Service\MicroPost\User\GetFollowersFollowingOfUserService;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class GetFollowersFollowingOfUserServiceTest extends TestCase
{
    public function testGetFollowersFollowingOfUserServiceWithRemainder(): void
    {
        $srv = new GetFollowersFollowingOfUserService(2, 4);
        $user = self::createMock(User::class);

        // Test followers - show first 2 user of 5
        $user->expects(self::once())
            ->method('getFollowers')
            ->willReturn($this->getUserCollection(5));

        $dtoFollowers = $srv->getDtoFollowers($user);

        self::assertEquals(5, $dtoFollowers->total);
        self::assertEquals(3, $dtoFollowers->remainder);
        self::assertCount(2, $dtoFollowers->collection);

        // Test followings - show first 4 user of 5
        $user->expects(self::once())
            ->method('getFollowing')
            ->willReturn($this->getUserCollection(5));

        $dtoFollowings = $srv->getDtoFollowings($user);

        self::assertEquals(5, $dtoFollowings->total);
        self::assertEquals(1, $dtoFollowings->remainder);
        self::assertCount(4, $dtoFollowings->collection);
    }

    public function testGetFollowersFollowingOfUserServiceWithoutRemainder(): void
    {
        $srv = new GetFollowersFollowingOfUserService(5, 10);
        $user = self::createMock(User::class);

        $user->expects(self::once())
            ->method('getFollowers')
            ->willReturn($this->getUserCollection(5));

        $dto = $srv->getDtoFollowers($user);

        self::assertEquals(5, $dto->total);
        self::assertEquals(0, $dto->remainder);
        self::assertCount(5, $dto->collection);

        $user->expects(self::once())
            ->method('getFollowing')
            ->willReturn($this->getUserCollection(10));

        $dto = $srv->getDtoFollowings($user);

        self::assertEquals(10, $dto->total);
        self::assertEquals(0, $dto->remainder);
        self::assertCount(10, $dto->collection);
    }

    protected function getUserCollection(int $count): ArrayCollection
    {
        $collection = new ArrayCollection();

        for ($i = 0; $i < $count; $i++) {
            $collection->add(new User());
        }

        return $collection;
    }
}
