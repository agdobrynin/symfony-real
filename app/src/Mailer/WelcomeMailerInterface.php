<?php

namespace App\Mailer;

use App\Entity\User;

interface WelcomeMailerInterface
{
    public function send(User $user, string $locale): bool;
}
