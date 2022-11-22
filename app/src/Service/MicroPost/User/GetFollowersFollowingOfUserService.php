<?php
declare(strict_types=1);

namespace App\Service\MicroPost\User;

use App\Dto\PartOfCollectionDto;
use App\Entity\User;

class GetFollowersFollowingOfUserService implements GetFollowersFollowingOfUserServiceInterface
{
    private $followersFirst;
    private $followingsFirst;

    public function __construct(int $followersFirst, int $followingsFirst)
    {
        $this->followersFirst = $followersFirst;
        $this->followingsFirst = $followingsFirst;
    }

    public function getDtoFollowers(User $user): PartOfCollectionDto
    {
        return new PartOfCollectionDto($user->getFollowers(), $this->followersFirst);
    }

    public function getDtoFollowings(User $user): PartOfCollectionDto
    {
        return new PartOfCollectionDto($user->getFollowing(), $this->followingsFirst);
    }
}
