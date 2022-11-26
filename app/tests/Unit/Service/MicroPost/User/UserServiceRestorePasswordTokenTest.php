<?php
declare(strict_types=1);

namespace App\Tests\Unit\Service\MicroPost\User;

use App\Entity\User;
use App\Security\ConfirmationTokenGenerator;
use App\Service\MicroPost\User\UserServiceRestorePasswordToken;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserServiceRestorePasswordTokenTest extends TestCase
{
    public function testService(): void
    {
        $user = new User();

        $confirmTokenGenerator = self::createMock(ConfirmationTokenGenerator::class);
        $confirmTokenGenerator->expects(self::once())->method('getRandomSecureToken')
            ->willReturn('abc-abc-abc');

        $em = self::createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('persist')->with($user);
        $em->expects(self::once())->method('flush');

        $tokenStorage = self::createMock(TokenStorageInterface::class);
        $tokenStorage->expects(self::once())
            ->method('setToken')
            ->with(null);

        $srv = new UserServiceRestorePasswordToken($confirmTokenGenerator, $em, $tokenStorage);
        $srv->refreshAndUnsetAuthToken($user);

        self::assertEquals('abc-abc-abc', $user->getChangePasswordToken());
    }
}
