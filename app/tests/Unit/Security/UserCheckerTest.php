<?php

namespace App\Tests\Unit\Security;

use App\Entity\User;
use App\Security\UserChecker;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserCheckerTest extends TestCase
{

    public function getUsers(): \Generator
    {
        yield [(new User())->setIsActive(false)];
        yield [(new User())->setIsActive(true)];
    }

    /**
     * @dataProvider getUsers
     */
    public function testCheckPreAuth(User $user): void
    {
        $em = self::createMock(EntityManagerInterface::class);

        $userChecker = (new UserChecker($em));

        if (!$user->getIsActive()) {
            $this->expectException(CustomUserMessageAccountStatusException::class);
        } else {
            $this->expectNotToPerformAssertions();
        }

        $userChecker->checkPreAuth($user);
    }

    public function testCheckPostAuth(): void
    {
        $user = new User();
        $em = self::createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('persist');
        $em->expects(self::once())->method('flush');

        self::assertNull($user->getLastLoginTime());

        (new UserChecker($em))->checkPostAuth($user);

        self::assertNotNull($user->getLastLoginTime());
        self::assertInstanceOf(\DateTimeInterface::class, $user->getLastLoginTime());
    }
}
