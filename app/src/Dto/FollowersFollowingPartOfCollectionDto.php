<?php
declare(strict_types=1);

namespace App\Dto;

class FollowersFollowingPartOfCollectionDto
{
    public $followers;
    public $following;

    public function __construct(PartOfCollectionDto $followers, PartOfCollectionDto $follow)
    {
        $this->followers = $followers;
        $this->following = $follow;
    }
}
