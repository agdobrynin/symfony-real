<?php
declare(strict_types=1);

namespace App\Tests\Integration\Service\MicroPost\User;

use App\Entity\User;
use App\Service\MicroPost\User\Exception\UserWrongPasswordException;
use App\Service\MicroPost\User\UserServiceChangePasswordInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserServiceChangePasswordTest extends KernelTestCase
{
    /** @var \Doctrine\Persistence\ObjectManager */
    protected $em;
    /** @var UserPasswordHasherInterface */
    protected $hasher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = self::getContainer()->get('doctrine')->getManager();
        $this->hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }

    public function getSourceDataForChangePassword(): \Generator
    {
        $passwordCurrent = 'password';

        $user = (new User())
            ->setLogin('user')
            ->setNick('user nick')
            ->setEmail('user@domain.com')
            ->setIsActive(true)
            ->setRoles(User::ROLE_DEFAULT);

        /** @var UserPasswordHasherInterface $hasher */
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $user->setPassword($hasher->hashPassword($user, $passwordCurrent));

        yield 'success' => [$user, $passwordCurrent, 'password2', null];
        yield 'fail' => [$user, 'wrong-password', 'password2', UserWrongPasswordException::class];
    }

    /**
     * @dataProvider getSourceDataForChangePassword
     */
    public function testChangePassword(User $user, string $passwordCurrent, string $passwordNew, ?string $expectException): void
    {
        /** @var UserServiceChangePasswordInterface $srv */
        $srv = self::getContainer()->get(UserServiceChangePasswordInterface::class);

        if ($expectException) {
            self::expectException($expectException);
        }

        $srv->changeAndResetAuthToken($user, $passwordCurrent, $passwordNew);

        $this->em->refresh($user);

        // deactivate user
        self::assertFalse($user->getIsActive());
        // password was changed
        self::assertTrue($this->hasher->isPasswordValid($user, $passwordNew));
        // user has confirmation token value
        self::assertMatchesRegularExpression('/[a-z0-9]{20,}/', $user->getConfirmationToken());
    }
}
