<?php

namespace App\Mailer;

use App\Entity\MicroPost;
use App\Entity\User;

interface LikeMailerInterface
{
    public function send(MicroPost $post, User $likedByUser, string $locale): bool;
}
