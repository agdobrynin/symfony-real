<?php
declare(strict_types=1);

namespace App\Tests\Unit\Service\MicroPost\User;

use App\Entity\User;
use App\Security\ConfirmationTokenGenerator;
use App\Service\MicroPost\LocalesInterface;
use App\Service\MicroPost\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserServiceMethodNewTest extends TestCase
{
    public function testNew(): void
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

        $tokenStorage = self::createMock(TokenStorageInterface::class);
        $tokenStorage->expects(self::never())->method('getToken');

        $src = new UserService($hasher, $confirmTokenGenerator, $locales, $em, $tokenStorage);
        $src->new($user, $passwordPlain, null);

        self::assertFalse($user->getIsActive());
        self::assertEquals($defaultLocale, $user->getPreferences()->getLocale());
        self::assertEquals($passwordHashed, $user->getPassword());
        self::assertSame(User::ROLE_DEFAULT, $user->getRoles());
        self::assertEquals($confirmToken, $user->getConfirmationToken());
    }
}
