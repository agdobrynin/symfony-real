<?php

namespace App\Service\MicroPost\User;

use App\Dto\PartOfCollectionDto;
use App\Entity\User;

interface GetFollowersFollowingOfUserServiceInterface
{
    public function getDtoFollowers(User $user, int $getFirstRecord): PartOfCollectionDto;

    public function getDtoFollowings(User $user, int $getFirstRecord): PartOfCollectionDto;
}
