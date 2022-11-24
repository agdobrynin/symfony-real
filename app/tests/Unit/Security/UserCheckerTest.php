<?php

namespace App\Tests\Unit\Security;

use App\Entity\User;
use App\Security\UserChecker;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserCheckerTest extends TestCase
{
    protected function getCustomUser(): UserInterface
    {
        return new class implements UserInterface {
            public function getRoles()
            {
            }

            public function getPassword()
            {
            }

            public function getSalt()
            {
            }

            public function eraseCredentials()
            {
            }

            public function getUsername()
            {
            }

            public function __call($name, $arguments)
            {
            }
        };
    }

    public function getUsers(): \Generator
    {
        yield 'user is not active yet' => [new User(), false];
        yield 'user is success pre auth' => [new User(), true];
        yield 'custom user not support' => [$this->getCustomUser(), null];
    }

    /**
     * @dataProvider getUsers
     */
    public function testCheckPreAuth(UserInterface $user, ?bool $isActive): void
    {
        $em = self::createMock(EntityManagerInterface::class);

        $userChecker = (new UserChecker($em));

        if ($user instanceof User && null !== $isActive) {
            $user->setIsActive($isActive);

            if (false === $isActive) {
                self::expectException(CustomUserMessageAccountStatusException::class);
            }
        }

        $userChecker->checkPreAuth($user);
        self::expectNotToPerformAssertions();
    }

    public function testCheckPostAuth(): void
    {
        $user = new User();
        $em = self::createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('persist')->with($user);
        $em->expects(self::once())->method('flush');

        self::assertNull($user->getLastLoginTime());

        (new UserChecker($em))->checkPostAuth($user);

        self::assertNotNull($user->getLastLoginTime());
        self::assertInstanceOf(\DateTimeInterface::class, $user->getLastLoginTime());
    }
}
