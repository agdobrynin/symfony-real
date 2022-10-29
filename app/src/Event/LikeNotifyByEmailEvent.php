<?php
declare(strict_types=1);

namespace App\Event;

use App\Entity\MicroPost;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class LikeNotifyByEmailEvent extends Event
{
    public const NAME = 'like.notify.by.email';

    private $microPost;
    private $likeByUser;

    public function __construct(MicroPost $microPost, User $likeByUser)
    {
        $this->microPost = $microPost;
        $this->likeByUser = $likeByUser;
    }

    public function getMicroPost(): MicroPost
    {
        return $this->microPost;
    }

    public function getLikedByUser(): User
    {
        return $this->likeByUser;
    }
}
