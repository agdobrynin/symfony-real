<?php

namespace App\Tests\Integration\Service\MicroPost\User;

use App\Entity\User;
use App\Service\MicroPost\User\Exception\SetConfirmationTokenForActiveUser;
use App\Service\MicroPost\User\Exception\UserWrongPasswordException;
use App\Service\MicroPost\User\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserServiceTest extends KernelTestCase
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

    public function testNewUser(): void
    {
        /** @var UserServiceInterface $srv */
        $srv = self::getContainer()->get(UserServiceInterface::class);

        $user = (new User())
            ->setLogin('user')
            ->setEmail('user@domain.com')
            ->setNick('User nick')
            ->setIsActive(true)
            ->setConfirmationToken(null);
        $userLocale = 'ru';

        $srv->new($user, 'abcdfg', $userLocale);

        $this->em->refresh($user);

        self::assertFalse($user->getIsActive());
        self::assertMatchesRegularExpression('/[a-z0-9]{20,}/', $user->getConfirmationToken());
        self::assertEquals($userLocale, $user->getPreferences()->getLocale());
        self::assertNotNull($user->getPassword());
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

        yield [$user, $passwordCurrent, 'password2', null];
        yield [$user, 'wrong-password', 'password2', UserWrongPasswordException::class];
    }

    /**
     * @dataProvider getSourceDataForChangePassword
     */
    public function testChangePassword(User $user, string $passwordCurrent, string $passwordNew, ?string $expectException): void
    {
        /** @var UserServiceInterface $srv */
        $srv = self::getContainer()->get(UserServiceInterface::class);

        if ($expectException) {
            self::expectException($expectException);
        }

        $srv->changePasswordAndResetAuthToken($user, $passwordCurrent, $passwordNew);

        $this->em->refresh($user);

        // deactivate user
        self::assertFalse($user->getIsActive());
        // password was changed
        self::assertTrue($this->hasher->isPasswordValid($user, $passwordNew));
        // user has confirmation token value
        self::assertMatchesRegularExpression('/[a-z0-9]{20,}/', $user->getConfirmationToken());
    }

    public function testChangeEmailSuccess(): void
    {
        $oldEmail = 'old@mail.com';
        $newEmail = 'new@mail.com';

        $user = (new User())
            ->setLogin('user')
            ->setEmail($oldEmail)
            ->setNick('User nick')
            ->setIsActive(true)
            ->setRoles(User::ROLE_DEFAULT);

        $user->setPassword($this->hasher->hashPassword($user, 'password'));

        /** @var UserServiceInterface $srv */
        $srv = self::getContainer()->get(UserServiceInterface::class);

        $srv->changeEmailAndResetAuthToken($user, $newEmail);

        $this->em->refresh($user);

        self::assertEquals($newEmail, $user->getEmail());
        self::assertFalse($user->getIsActive());
        self::assertMatchesRegularExpression('/[a-z0-9]{20,}/', $user->getConfirmationToken());
    }
}
