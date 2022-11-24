<?php
declare(strict_types=1);

namespace App\Service\MicroPost\User;

use App\Entity\User;
use App\Security\ConfirmationTokenGeneratorInterface;
use App\Service\MicroPost\User\Exception\SetConfirmationTokenForActiveUser;
use Doctrine\ORM\EntityManagerInterface;

class UserServiceRefreshConfirmToken implements UserServiceRefreshConfirmTokenInterface
{
    private $confirmationTokenGenerator;
    private $entityManager;

    public function __construct(
        ConfirmationTokenGeneratorInterface $confirmationTokenGenerator,
        EntityManagerInterface              $entityManager
    )
    {
        $this->confirmationTokenGenerator = $confirmationTokenGenerator;
        $this->entityManager = $entityManager;
    }

    public function refresh(User $user): void
    {
        if ($user->getIsActive()) {
            throw new SetConfirmationTokenForActiveUser();
        }

        $confirmToken = $this->confirmationTokenGenerator->getRandomSecureToken();
        $user->setConfirmationToken($confirmToken);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
