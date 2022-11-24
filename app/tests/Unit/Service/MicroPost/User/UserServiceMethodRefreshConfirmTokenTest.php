<?php
declare(strict_types=1);

namespace App\Tests\Unit\Service\MicroPost\User;

use App\Entity\User;
use App\Security\ConfirmationTokenGenerator;
use App\Service\MicroPost\LocalesInterface;
use App\Service\MicroPost\User\Exception\SetConfirmationTokenForActiveUser;
use App\Service\MicroPost\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserServiceMethodRefreshConfirmTokenTest extends TestCase
{
    public function testRefreshConfirmTokenSuccess(): void
    {
        $user = (new User())->setIsActive(false);
        $confirmToken = 'abc-abc-abc';

        $hasher = self::createMock(UserPasswordHasher::class);
        $hasher->expects(self::never())->method('hashPassword');

        $confirmTokenGenerator = self::createMock(ConfirmationTokenGenerator::class);
        $confirmTokenGenerator->expects(self::once())->method('getRandomSecureToken')
            ->with(40)->willReturn($confirmToken);

        $locales = self::createMock(LocalesInterface::class);
        $locales->expects(self::never())->method('getLocales');

        $em = self::createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('persist')->with($user);
        $em->expects(self::once())->method('flush');

        $tokenStorage = self::createMock(TokenStorageInterface::class);
        $tokenStorage->expects(self::never())->method('getToken');

        $src = new UserService($hasher, $confirmTokenGenerator, $locales, $em, $tokenStorage);

        $src->refreshConfirmToken($user);

        self::assertEquals($confirmToken, $user->getConfirmationToken());
        self::assertFalse($user->getIsActive());
    }

    public function testRefreshConfirmTokenFail(): void
    {
        $user = (new User())->setIsActive(true);

        $hasher = self::createMock(UserPasswordHasher::class);
        $hasher->expects(self::never())->method('hashPassword');

        $confirmTokenGenerator = self::createMock(ConfirmationTokenGenerator::class);
        $confirmTokenGenerator->expects(self::never())->method('getRandomSecureToken');

        $locales = self::createMock(LocalesInterface::class);
        $locales->expects(self::never())->method('getLocales');

        $em = self::createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('persist')->with($user);
        $em->expects(self::never())->method('flush');

        $tokenStorage = self::createMock(TokenStorageInterface::class);
        $tokenStorage->expects(self::never())->method('getToken');

        $src = new UserService($hasher, $confirmTokenGenerator, $locales, $em, $tokenStorage);

        self::expectException(SetConfirmationTokenForActiveUser::class);

        $src->refreshConfirmToken($user);
    }
}
