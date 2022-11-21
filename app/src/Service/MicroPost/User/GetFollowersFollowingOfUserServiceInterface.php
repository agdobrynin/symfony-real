<?php

namespace App\Service\MicroPost\User;

use App\Dto\FollowersFollowingPartOfCollectionDto;
use Doctrine\Common\Collections\Collection;

interface GetFollowersFollowingOfUserServiceInterface
{
    public function getDto(Collection $followers, Collection $following): FollowersFollowingPartOfCollectionDto;
}
