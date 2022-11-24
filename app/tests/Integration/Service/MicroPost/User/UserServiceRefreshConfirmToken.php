<?php
declare(strict_types=1);

namespace App\Tests\Integration\Service\MicroPost\User;

use App\Entity\User;
use App\Service\MicroPost\User\Exception\SetConfirmationTokenForActiveUser;
use App\Service\MicroPost\User\UserServiceRefreshConfirmTokenInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserServiceRefreshConfirmToken extends KernelTestCase
{
    public function getDataSourceForRefreshConfirmToken(): \Generator
    {
        yield 'Success refresh confirm token' => [
            (new User())->setIsActive(false)
                ->setPassword('abc')
                ->setRoles(User::ROLE_DEFAULT)
                ->setLogin('john')
                ->setNick('John')
                ->setEmail('john@ok.com')
        ];

        yield 'Fail refresh confirm token for active user' => [
            (new User())->setIsActive(true)
                ->setPassword('abc')
                ->setRoles(User::ROLE_DEFAULT)
                ->setLogin('john1')
                ->setNick('John 1')
                ->setEmail('john1@ok.com')
        ];
    }

    /**
     * @dataProvider getDataSourceForRefreshConfirmToken
     */
    public function testRefreshConfirmToken(User $user): void
    {
        if ($user->getIsActive()) {
            self::expectException(SetConfirmationTokenForActiveUser::class);
        }

        /** @var UserServiceRefreshConfirmTokenInterface $srv */
        $srv = self::getContainer()->get(UserServiceRefreshConfirmTokenInterface::class);
        $srv->refresh($user);

        self::assertFalse($user->getIsActive());
        self::assertNotEmpty($user->getConfirmationToken());
    }
}
