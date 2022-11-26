<?php
declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Security\Exception\LoginNotConfirmAccountStatusException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->getIsActive()) {
            $message = 'Your user account is not confirm. Please look for an email in your inbox with a confirmation link.';

            throw new LoginNotConfirmAccountStatusException($message);
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if ($user instanceof User) {
            $user->setLastLoginTime(new \DateTime());
            // if user has token for restore password - unset token because user login successfully.
            $user->setChangePasswordToken(null);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }
}
