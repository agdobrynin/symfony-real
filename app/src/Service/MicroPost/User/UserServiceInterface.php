<?php

namespace App\Service\MicroPost\User;

use App\Entity\User;
use App\Service\MicroPost\User\Exception\UserWrongPasswordException;

interface UserServiceInterface
{
    public function new(User $user, string $passwordPlain, ?string $userLocale): void;

    /**
     * @throws UserWrongPasswordException When current password is wrong.
     */
    public function changePassword(User $user, string $currentPasswordPlain, string $newPasswordPlain): void;

    public function changeEmail(User $user, string $newEmail): bool;
}
