<?php

namespace App\Service\MicroPost\User;

use App\Entity\User;

interface UserServiceInterface
{
    public function new(User $user, string $passwordPlain, ?string $userLocale): void;

    /**
     * @throws \App\Service\MicroPost\User\Exception\SetConfirmationTokenForActiveUser If user is active.
     */
    public function refreshConfirmToken(User $user): void;

    /**
     * @throws \App\Service\MicroPost\User\Exception\UserWrongPasswordException When current password is wrong.
     */
    public function changePasswordAndResetAuthToken(User $user, string $currentPasswordPlain, string $newPasswordPlain): void;

    public function changeEmailAndResetAuthToken(User $user, string $newEmail): void;
}
