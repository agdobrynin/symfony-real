<?php
declare(strict_types=1);

namespace App\Service\MicroPost\User;

use App\Entity\User;
use App\Entity\UserPreferences;
use App\Security\ConfirmationTokenGeneratorInterface;
use App\Service\MicroPost\LocalesInterface;
use App\Service\MicroPost\User\Exception\SetConfirmationTokenForActiveUser;
use App\Service\MicroPost\User\Exception\UserWrongPasswordException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserService implements UserServiceInterface
{
    private $userPasswordHasher;
    private $confirmationTokenGenerator;
    private $locales;
    private $entityManager;
    private $tokenStorage;

    public function __construct(
        UserPasswordHasherInterface         $userPasswordHasher,
        ConfirmationTokenGeneratorInterface $confirmationTokenGenerator,
        LocalesInterface                    $locales,
        EntityManagerInterface              $entityManager,
        TokenStorageInterface               $tokenStorage
    )
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->confirmationTokenGenerator = $confirmationTokenGenerator;
        $this->locales = $locales;
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function new(User $user, string $passwordPlain, ?string $userLocale): void
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

    public function refreshConfirmToken(User $user): void
    {
        if ($user->getIsActive()) {
            throw new SetConfirmationTokenForActiveUser();
        }

        $confirmToken = $this->confirmationTokenGenerator->getRandomSecureToken();
        $user->setConfirmationToken($confirmToken);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function changePasswordAndResetAuthToken(User $user, string $currentPasswordPlain, string $newPasswordPlain): void
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

    public function changeEmailAndResetAuthToken(User $user, string $newEmail): void
    {
        $user->setEmail($newEmail);
        $user->setIsActive(false);
        $user->setConfirmationToken($this->confirmationTokenGenerator->getRandomSecureToken());
        $this->tokenStorage->setToken();
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
