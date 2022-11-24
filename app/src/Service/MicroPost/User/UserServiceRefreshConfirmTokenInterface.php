<?php

namespace App\Service\MicroPost\User;

use App\Entity\User;

interface UserServiceRefreshConfirmTokenInterface
{
    /**
     * @throws \App\Service\MicroPost\User\Exception\SetConfirmationTokenForActiveUser If user is active.
     */
    public function refresh(User $user): void;
}
