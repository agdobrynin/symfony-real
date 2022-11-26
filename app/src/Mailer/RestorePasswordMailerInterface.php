<?php

namespace App\Mailer;

use App\Entity\User;

interface RestorePasswordMailerInterface
{
    /**
     * @throws \App\Mailer\Exception\ChangePasswordTokenException
     */
    public function send(User $user): bool;
}
