<?php

namespace App\Tests\Unit\Security;

use App\Entity\User;
use App\Security\UserChecker;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserCheckerTest extends TestCase
{
    /**
     * @dataProvider getUsers
     */
    public function testUserCheckerIsNotActive(User $user): void
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

    public function getUsers(): \Generator
    {
        yield [(new User())->setIsActive(false)];
        yield [(new User())->setIsActive(true)];
    }
}
