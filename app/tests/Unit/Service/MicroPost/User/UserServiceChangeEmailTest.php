<?php
declare(strict_types=1);

namespace App\Tests\Unit\Service\MicroPost\User;

use App\Entity\User;
use App\Security\ConfirmationTokenGenerator;
use App\Service\MicroPost\User\UserServiceChangeEmail;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserServiceChangeEmailTest extends TestCase
{
    public function testChangeEmail(): void
    {
        $oldEmail = 'old@domain.com';
        $newEmail = 'new@domain.com';
        $confirmToken = 'abc-abc-abc';

        $user = (new User())->setEmail($oldEmail)->setIsActive(true);

        $em = self::createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('persist')->with($user);
        $em->expects(self::once())->method('flush');

        $confirmTokenGenerator = self::createMock(ConfirmationTokenGenerator::class);
        $confirmTokenGenerator->expects(self::once())->method('getRandomSecureToken')
            ->with(40)->willReturn($confirmToken);

        $tokenStorage = self::createMock(TokenStorageInterface::class);
        $tokenStorage->expects(self::once())
            ->method('setToken')
            ->with(null);

        $srv = new UserServiceChangeEmail($confirmTokenGenerator, $em, $tokenStorage);
        $srv->changeAndResetAuthToken($user, $newEmail);

        self::assertFalse($user->getIsActive());
        self::assertEquals($newEmail, $user->getEmail());
        self::assertEquals($confirmToken, $user->getConfirmationToken());
    }
}
