<?php
declare(strict_types=1);

namespace App\Service\MicroPost\User;

use App\Entity\User;
use App\Service\MicroPost\User\Exception\PasswordTokenInvalidException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserServiceRestoredPassword implements UserServiceRestoredPasswordInterface
{
    private $hasher;
    private $em;
    private $tokenStorage;

    public function __construct(
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface      $em,
        TokenStorageInterface       $tokenStorage
    )
    {
        $this->hasher = $hasher;
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    public function updateAndUnsetAuthToken(User $user, string $plainPassword, string $changePasswordToken): void
    {
        if ($user->getChangePasswordToken() !== $changePasswordToken) {
            throw new PasswordTokenInvalidException();
        }

        $passwordHash = $this->hasher->hashPassword($user, $plainPassword);
        $user->setPassword($passwordHash);
        $user->setChangePasswordToken(null);
        $this->em->persist($user);
        $this->em->flush();
        $this->tokenStorage->setToken(null);
    }
}
