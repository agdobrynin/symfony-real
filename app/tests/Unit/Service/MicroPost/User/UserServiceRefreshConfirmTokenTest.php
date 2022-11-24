<?php
declare(strict_types=1);

namespace App\Tests\Unit\Service\MicroPost\User;

use App\Entity\User;
use App\Security\ConfirmationTokenGenerator;
use App\Service\MicroPost\User\Exception\SetConfirmationTokenForActiveUser;
use App\Service\MicroPost\User\UserServiceRefreshConfirmToken;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class UserServiceRefreshConfirmTokenTest extends TestCase
{
    public function testRefreshConfirmTokenSuccess(): void
    {
        $user = (new User())->setIsActive(false);
        $confirmToken = 'abc-abc-abc';

        $confirmTokenGenerator = self::createMock(ConfirmationTokenGenerator::class);
        $confirmTokenGenerator->expects(self::once())->method('getRandomSecureToken')
            ->with(40)->willReturn($confirmToken);

        $em = self::createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('persist')->with($user);
        $em->expects(self::once())->method('flush');

        $srv = new UserServiceRefreshConfirmToken($confirmTokenGenerator, $em);

        $srv->refresh($user);

        self::assertEquals($confirmToken, $user->getConfirmationToken());
        self::assertFalse($user->getIsActive());
    }

    public function testRefreshConfirmTokenFail(): void
    {
        $user = (new User())->setIsActive(true);

        $confirmTokenGenerator = self::createMock(ConfirmationTokenGenerator::class);
        $confirmTokenGenerator->expects(self::never())->method('getRandomSecureToken');

        $em = self::createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('persist')->with($user);
        $em->expects(self::never())->method('flush');

        $srv = new UserServiceRefreshConfirmToken($confirmTokenGenerator, $em);

        self::expectException(SetConfirmationTokenForActiveUser::class);

        $srv->refresh($user);
    }
}
