<?php
declare(strict_types=1);

namespace App\Service\MicroPost\User;

use App\Entity\User;
use App\Security\ConfirmationTokenGeneratorInterface;
use App\Service\MicroPost\User\Exception\UserWrongPasswordException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserServiceChangePassword implements UserServiceChangePasswordInterface
{
    private $userPasswordHasher;
    private $confirmationTokenGenerator;
    private $entityManager;
    private $tokenStorage;

    public function __construct(
        UserPasswordHasherInterface         $userPasswordHasher,
        ConfirmationTokenGeneratorInterface $confirmationTokenGenerator,
        EntityManagerInterface              $entityManager,
        TokenStorageInterface               $tokenStorage
    )
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->confirmationTokenGenerator = $confirmationTokenGenerator;
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function changeAndResetAuthToken(User $user, string $currentPasswordPlain, string $newPasswordPlain): void
    {
        if (!$this->userPasswordHasher->isPasswordValid($user, $currentPasswordPlain)) {
            throw new UserWrongPasswordException();
        }

        $passwordHash = $this->userPasswordHasher->hashPassword($user, $newPasswordPlain);
        $user->setPassword($passwordHash);
        $user->setIsActive(false);
        $user->setConfirmationToken($this->confirmationTokenGenerator->getRandomSecureToken());
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->tokenStorage->setToken();
    }
}
