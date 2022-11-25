<?php
declare(strict_types=1);

namespace App\Tests\Unit\Service\MicroPost\User;

use App\Entity\User;
use App\Service\MicroPost\User\Exception\PasswordTokenInvalidException;
use App\Service\MicroPost\User\UserServiceRestoredPassword;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserServiceRestoredPasswordTest extends TestCase
{
    public function getSourceData(): \Generator
    {
        yield 'success' => [
            (new User())->setChangePasswordToken('token'),
            'password',
            'hashed-password',
            'token',
            null,
        ];

        yield 'fail not set change password token' => [
            new User(),
            'password',
            'hashed-password',
            'token',
            PasswordTokenInvalidException::class,
        ];

        yield 'fail change password token not equal in user' => [
            (new User())->setChangePasswordToken('true-token'),
            'password',
            'hashed-password',
            'false-token',
            PasswordTokenInvalidException::class,
        ];
    }

    /**
     * @dataProvider getSourceData
     */
    public function testService(
        User    $user,
        string  $password,
        string  $hashedPassword,
        string  $changePasswordToken,
        ?string $expectException
    ): void
    {

        $expects = $expectException ? self::never() : self::any();

        $hasher = self::createMock(UserPasswordHasher::class);
        $hasher->expects($expects)->method('hashPassword')
            ->with($user, $password)->willReturn($hashedPassword);

        $em = self::createMock(EntityManagerInterface::class);
        $em->expects($expects)->method('persist')->with($user);
        $em->expects($expects)->method('flush');

        $tokenStorage = self::createMock(TokenStorageInterface::class);
        $tokenStorage->expects($expects)
            ->method('setToken')
            ->with(null);

        if ($expectException) {
            self::expectException($expectException);
        }

        $srv = new UserServiceRestoredPassword($hasher, $em, $tokenStorage);

        $srv->updateAndUnsetAuthToken($user, $password, $changePasswordToken);

        self::assertNull($user->getChangePasswordToken());
        self::assertEquals($hashedPassword, $user->getPassword());
    }
}
