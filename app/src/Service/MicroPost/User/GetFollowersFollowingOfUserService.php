<?php
declare(strict_types=1);

namespace App\Service\MicroPost\User;

use App\Dto\FollowersFollowingPartOfCollectionDto;
use App\Dto\PartOfCollectionDto;
use Doctrine\Common\Collections\Collection;

class GetFollowersFollowingOfUserService implements GetFollowersFollowingOfUserServiceInterface
{
    private $followersFirst;
    private $followingFirst;

    public function __construct(int $followersFirst, int $followingFirst)
    {
        $this->followersFirst = $followersFirst;
        $this->followingFirst = $followingFirst;
    }

    public function getDto(Collection $followers, Collection $following): FollowersFollowingPartOfCollectionDto
    {
        $partOfFollowers = new PartOfCollectionDto($followers, $this->followersFirst);
        $partOfFollowing = new PartOfCollectionDto($following, $this->followingFirst);

        return new FollowersFollowingPartOfCollectionDto($partOfFollowers, $partOfFollowing);
    }
}
