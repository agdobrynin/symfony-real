<?php

namespace App\Mailer;

use App\Entity\User;

interface PasswordChangeMailerInterface
{
    public function send(User $user): bool;
}
