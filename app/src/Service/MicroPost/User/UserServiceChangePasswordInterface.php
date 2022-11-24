<?php

namespace App\Service\MicroPost\User;

use App\Entity\User;

interface UserServiceChangePasswordInterface
{
    /**
     * @throws \App\Service\MicroPost\User\Exception\UserWrongPasswordException When current password is wrong.
     */
    public function changeAndResetAuthToken(User $user, string $currentPasswordPlain, string $newPasswordPlain): void;
}
