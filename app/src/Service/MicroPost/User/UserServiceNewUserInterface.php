<?php

namespace App\Service\MicroPost\User;

use App\Entity\User;

interface UserServiceNewUserInterface
{
    public function addAndSetConfirmationToken(User $user, string $passwordPlain, ?string $userLocale): void;
}
