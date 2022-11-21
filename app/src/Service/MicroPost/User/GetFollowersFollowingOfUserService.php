<?php
declare(strict_types=1);

namespace App\Service\MicroPost\User;

use App\Dto\PartOfCollectionDto;
use App\Entity\User;

class GetFollowersFollowingOfUserService implements GetFollowersFollowingOfUserServiceInterface
{
    public function getDtoFollowers(User $user, int $getFirstRecord): PartOfCollectionDto
    {
        return new PartOfCollectionDto($user->getFollowers(), $getFirstRecord);
    }

    public function getDtoFollowings(User $user, int $getFirstRecord): PartOfCollectionDto
    {
        return new PartOfCollectionDto($user->getFollowing(), $getFirstRecord);
    }
}
