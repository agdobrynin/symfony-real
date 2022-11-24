<?php
declare(strict_types=1);

namespace App\Service\MicroPost\User;

use App\Entity\User;
use App\Entity\UserPreferences;
use App\Security\ConfirmationTokenGeneratorInterface;
use App\Service\MicroPost\LocalesInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserServiceNewUser implements UserServiceNewUserInterface
{
    private $userPasswordHasher;
    private $confirmationTokenGenerator;
    private $locales;
    private $entityManager;

    public function __construct(
        UserPasswordHasherInterface         $userPasswordHasher,
        ConfirmationTokenGeneratorInterface $confirmationTokenGenerator,
        LocalesInterface                    $locales,
        EntityManagerInterface              $entityManager
    )
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->confirmationTokenGenerator = $confirmationTokenGenerator;
        $this->locales = $locales;
        $this->entityManager = $entityManager;
    }

    public function addAndSetConfirmationToken(User $user, string $passwordPlain, ?string $userLocale): void
    {
        // encode the plain password
        $passwordHash = $this->userPasswordHasher->hashPassword($user, $passwordPlain);
        $user->setPassword($passwordHash);
        $user->setRoles(User::ROLE_DEFAULT);
        $user->setIsActive(false);
        $user->setConfirmationToken($this->confirmationTokenGenerator->getRandomSecureToken());

        $locale = $userLocale ?: $this->locales->getDefaultLocale();
        $preferences = (new UserPreferences())->setLocale($locale);
        $user->setPreferences($preferences);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
