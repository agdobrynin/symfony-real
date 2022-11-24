<?php

namespace App\Service\MicroPost\User;

use App\Entity\User;

interface UserServiceChangeEmailInterface
{
    public function changeAndResetAuthToken(User $user, string $newEmail): void;
}
