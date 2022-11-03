<?php

namespace App\Service\MicroPost;

use App\Entity\User;

interface WelcomeMessageEmailServiceInterface
{
    public function send(User $user): bool;
}
