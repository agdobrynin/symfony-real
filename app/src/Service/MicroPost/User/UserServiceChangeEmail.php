<?php
declare(strict_types=1);

namespace App\Service\MicroPost\User;

use App\Entity\User;
use App\Security\ConfirmationTokenGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserServiceChangeEmail implements UserServiceChangeEmailInterface
{
    private $confirmationTokenGenerator;
    private $entityManager;
    private $tokenStorage;

    public function __construct(
        ConfirmationTokenGeneratorInterface $confirmationTokenGenerator,
        EntityManagerInterface              $entityManager,
        TokenStorageInterface               $tokenStorage
    )
    {
        $this->confirmationTokenGenerator = $confirmationTokenGenerator;
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function changeAndResetAuthToken(User $user, string $newEmail): void
    {
        $user->setEmail($newEmail);
        $user->setIsActive(false);
        $user->setConfirmationToken($this->confirmationTokenGenerator->getRandomSecureToken());
        $this->tokenStorage->setToken();
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
