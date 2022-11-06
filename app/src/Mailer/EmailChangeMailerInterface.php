<?php

namespace App\Mailer;

use App\Entity\User;

interface EmailChangeMailerInterface
{
    public function send(User $user): bool;
}
