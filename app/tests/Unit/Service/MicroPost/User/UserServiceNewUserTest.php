<?php
declare(strict_types=1);

namespace App\Tests\Unit\Service\MicroPost\User;

use App\Entity\User;
use App\Security\ConfirmationTokenGenerator;
use App\Service\MicroPost\LocalesInterface;
use App\Service\MicroPost\User\UserServiceNewUser;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;

class UserServiceNewUserTest extends TestCase
{
    public function testNewUser(): void
    {
        $passwordPlain = 'password';
        $passwordHashed = 'hashed-password';
        $confirmToken = 'abc-abc-abc';
        $defaultLocale = 'ru';

        $user = (new User())->setIsActive(true);

        $hasher = self::createMock(UserPasswordHasher::class);
        $hasher->expects(self::once())->method('hashPassword')
            ->with($user, $passwordPlain)->willReturn($passwordHashed);

        $confirmTokenGenerator = self::createMock(ConfirmationTokenGenerator::class);
        $confirmTokenGenerator->expects(self::once())->method('getRandomSecureToken')
            ->with(40)->willReturn($confirmToken);

        $locales = self::createMock(LocalesInterface::class);
        $locales->expects(self::once())->method('getDefaultLocale')
            ->willReturn($defaultLocale);

        $em = self::createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('persist')->with($user);
        $em->expects(self::once())->method('flush');

        $srv = new UserServiceNewUser($hasher, $confirmTokenGenerator, $locales, $em);
        $srv->addAndSetConfirmationToken($user, $passwordPlain, null);

        self::assertFalse($user->getIsActive());
        self::assertEquals($defaultLocale, $user->getPreferences()->getLocale());
        self::assertEquals($passwordHashed, $user->getPassword());
        self::assertSame(User::ROLE_DEFAULT, $user->getRoles());
        self::assertEquals($confirmToken, $user->getConfirmationToken());
    }
}
