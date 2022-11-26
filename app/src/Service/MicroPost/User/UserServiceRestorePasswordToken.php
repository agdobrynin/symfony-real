<?php
declare(strict_types=1);

namespace App\Service\MicroPost\User;

use App\Entity\User;
use App\Security\ConfirmationTokenGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserServiceRestorePasswordToken implements UserServiceRestorePasswordTokenInterface
{
    private $confirmationTokenGenerator;
    private $em;
    private $tokenStorage;

    public function __construct(
        ConfirmationTokenGeneratorInterface $confirmationTokenGenerator,
        EntityManagerInterface              $em,
        TokenStorageInterface               $tokenStorage
    )
    {
        $this->confirmationTokenGenerator = $confirmationTokenGenerator;
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    public function refreshAndUnsetAuthToken(User $user): void
    {
        $confirmToken = $this->confirmationTokenGenerator->getRandomSecureToken();
        $user->setChangePasswordToken($confirmToken);
        $this->em->persist($user);
        $this->em->flush();
        $this->tokenStorage->setToken(null);
    }
}
