<?php

namespace App\Service\MicroPost\User;

use App\Entity\User;

interface UserServiceRestorePasswordTokenInterface
{
    public function refreshAndUnsetAuthToken(User $user): void;
}
