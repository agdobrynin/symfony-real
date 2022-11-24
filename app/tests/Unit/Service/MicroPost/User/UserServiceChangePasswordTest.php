<?php
declare(strict_types=1);

namespace App\Tests\Unit\Service\MicroPost\User;

use App\Entity\User;
use App\Security\ConfirmationTokenGenerator;
use App\Service\MicroPost\User\Exception\UserWrongPasswordException;
use App\Service\MicroPost\User\UserServiceChangePassword;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserServiceChangePasswordTest extends TestCase
{
    public function getSourceData(): \Generator
    {
        $passwordCurrent = 'password';

        $user = (new User())->setIsActive(true)->setPassword($passwordCurrent);

        yield 'wrong' => [$user, 'wrong-password', 'password2', 'abc-abc', UserWrongPasswordException::class];
        yield 'success' => [$user, $passwordCurrent, 'password2', 'abc-abc', null];
    }

    /**
     * @dataProvider getSourceData
     */
    public function testChangePassword(User $user, string $passwordCurrent, string $passwordNew, string $confirmToken, ?string $expectException): void
    {
        $hasher = self::createMock(UserPasswordHasher::class);
        $hasher->expects(self::once())
            ->method('isPasswordValid')
            ->with($user, $passwordCurrent)
            ->willReturn(!$expectException);


        if ($expectException) {
            self::expectException($expectException);
        }

        $expects = $expectException ? self::never() : self::atLeastOnce();
        $em = self::createMock(EntityManagerInterface::class);
        $em->expects($expects)->method('persist')->with($user);
        $em->expects($expects)->method('flush');

        $confirmTokenGenerator = self::createMock(ConfirmationTokenGenerator::class);
        $confirmTokenGenerator->expects($expects)->method('getRandomSecureToken')
            ->with(40)->willReturn($confirmToken);

        $tokenStorage = self::createMock(TokenStorageInterface::class);
        $tokenStorage->expects($expects)
            ->method('setToken')
            ->with(null);

        $srv = new UserServiceChangePassword($hasher, $confirmTokenGenerator, $em, $tokenStorage);

        $srv->changeAndResetAuthToken($user, $passwordCurrent, $passwordNew);
    }
}
