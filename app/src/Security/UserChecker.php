<?php
declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->getIsActive()) {
            $message = 'Your user account is not confirm. 
            Please look for an email in your inbox with a confirmation link.';

            throw new CustomUserMessageAccountStatusException($message);
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // TODO: Implement checkPostAuth() method.
    }
}
