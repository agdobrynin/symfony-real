<?php

namespace App\Service\MicroPost\User;

use App\Entity\User;

interface UserServiceRestoredPasswordInterface
{
    /**
     * @throws \App\Service\MicroPost\User\Exception\PasswordTokenInvalidException
     */
    public function updateAndUnsetAuthToken(User $user, string $plainPassword, string $changePasswordToken): void;
}
