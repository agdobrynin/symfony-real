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
    private $locale;

    public function __construct(MicroPost $microPost, User $likeByUser, string $locale)
    {
        $this->microPost = $microPost;
        $this->likeByUser = $likeByUser;
        $this->locale = $locale;
    }

    public function getMicroPost(): MicroPost
    {
        return $this->microPost;
    }

    public function getLikedByUser(): User
    {
        return $this->likeByUser;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
