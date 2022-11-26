<?php
declare(strict_types=1);

namespace App\Tests\Integration\Service\MicroPost\User;

use App\Entity\User;
use App\Service\MicroPost\User\Exception\PasswordTokenInvalidException;
use App\Service\MicroPost\User\UserServiceRestoredPasswordInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserServiceRestoredPasswordTest extends KernelTestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }

    public function dataSource(): \Generator
    {
        yield 'success update' => [
            (new User())
                ->setRoles(User::ROLE_DEFAULT)
                ->setLogin('john1')
                ->setPassword('pass')
                ->setNick('John 1')
                ->setEmail('john1@ok.com')
                ->setChangePasswordToken('token'),
            'password123',
            'token',
            null,
        ];

        yield 'fail update - wrong change password token' => [
            (new User())
                ->setRoles(User::ROLE_DEFAULT)
                ->setLogin('john2')
                ->setPassword('pass2')
                ->setNick('John 2')
                ->setEmail('john2@ok.com')
                ->setChangePasswordToken('token'),
            'password123',
            'token-wrong',
            PasswordTokenInvalidException::class,
        ];
    }

    /**
     * @dataProvider dataSource
     */
    public function testService(
        User    $user,
        string  $password,
        string  $changePasswordToken,
        ?string $expectException
    ): void
    {
        $this->em->persist($user);
        $this->em->flush();

        if ($expectException) {
            self::expectException($expectException);
        }

        /** @var UserServiceRestoredPasswordInterface $srv */
        $srv = self::getContainer()->get(UserServiceRestoredPasswordInterface::class);
        $srv->updateAndUnsetAuthToken($user, $password, $changePasswordToken);

        $this->em->refresh($user);

        self::assertNull($user->getChangePasswordToken());

        /** @var UserPasswordHasher $hasher */
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertTrue($hasher->isPasswordValid($user, $password));
    }
}
