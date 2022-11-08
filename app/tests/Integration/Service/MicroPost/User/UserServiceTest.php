<?php

namespace App\Tests\Integration\Service\MicroPost\User;

use App\Entity\User;
use App\Security\ConfirmationTokenGenerator;
use App\Service\MicroPost\LocalesInterface;
use App\Service\MicroPost\User\Exception\UserWrongPasswordException;
use App\Service\MicroPost\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class UserServiceTest extends KernelTestCase
{
    /** @var UserPasswordHasherInterface */
    private $passwordHasher;
    private $confirmToken;
    private $locales;
    /**
     * @var EntityManagerInterface|EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject|\PHPUnit\Framework\MockObject\MockObject
     */
    private $entityManagerMock;

    public function setUp(): void
    {
        $this->passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $this->confirmToken = self::getContainer()->get(ConfirmationTokenGenerator::class);
        $this->locales = self::getContainer()->get(LocalesInterface::class);
    }

    public function assertPreConditions(): void
    {
        $this->entityManagerMock = self::createMock(EntityManagerInterface::class);
    }

    public function testNewUser(): void
    {
        $user = new User();
        $this->entityManagerMock->expects(self::once())->method('persist')->with($user);
        $this->entityManagerMock->expects(self::once())->method('flush');

        $userLocale = 'ru';

        $tokenStorage = self::getMockBuilder(TokenStorage::class)->getMock();
        $tokenStorage->expects(self::never())
            ->method('setToken');

        (new UserService($this->passwordHasher, $this->confirmToken, $this->locales, $this->entityManagerMock, $tokenStorage))
            ->new($user, 'pass', $userLocale);

        self::assertFalse($user->getIsActive());
        self::assertMatchesRegularExpression('/[a-z0-9]{20,}/', $user->getConfirmationToken());
        self::assertEquals($userLocale, $user->getPreferences()->getLocale());
        self::assertNotNull($user->getPassword());
    }

    public function testChangePassword(): void
    {
        $user = (new User())->setIsActive(true);
        $currentPassword = 'pass1';
        $user->setPassword($this->passwordHasher->hashPassword($user, $currentPassword));
        $newPassword = 'pass2';

        self::assertTrue($user->getIsActive());
        self::assertNotNull($user->getPassword());
        self::assertNull($user->getConfirmationToken());

        $this->entityManagerMock->expects(self::once())->method('persist')->with($user);
        $this->entityManagerMock->expects(self::once())->method('flush');

        $tokenStorage = self::getMockBuilder(TokenStorage::class)->getMock();
        $tokenStorage->expects(self::once())
            ->method('setToken')
            ->with(null);

        (new UserService($this->passwordHasher, $this->confirmToken, $this->locales, $this->entityManagerMock, $tokenStorage))
            ->changePasswordAndResetAuthToken($user, $currentPassword, $newPassword);

        self::assertFalse($user->getIsActive());
        self::assertNotNull($user->getPassword());
        self::assertMatchesRegularExpression('/[a-z0-9]{20,}/', $user->getConfirmationToken());
    }

    public function testChangePasswordWrongCurrentPassword()
    {
        $user = (new User())->setIsActive(true);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'pass1'));

        $this->entityManagerMock->expects(self::never())->method('persist')->with($user);
        $this->entityManagerMock->expects(self::never())->method('flush')->with($user);

        $tokenStorage = self::getMockBuilder(TokenStorage::class)->getMock();

        self::expectException(UserWrongPasswordException::class);

        (new UserService($this->passwordHasher, $this->confirmToken, $this->locales, $this->entityManagerMock, $tokenStorage))
            ->changePasswordAndResetAuthToken($user, 'wrongPass', 'pass2');
    }

    public function testChangeEmailSuccess(): void
    {
        $oldEmail = 'old@mail.com';
        $newEmail = 'new@mail.com';

        $user = (new User())
            ->setIsActive(true)
            ->setEmail($oldEmail);

        $this->entityManagerMock->expects(self::once())->method('persist')->with($user);
        $this->entityManagerMock->expects(self::once())->method('flush');

        $tokenStorage = self::getMockBuilder(TokenStorage::class)->getMock();
        $tokenStorage->expects(self::once())
            ->method('setToken')
            ->with(null);

        self::assertEquals($oldEmail, $user->getEmail());
        self::assertTrue($user->getIsActive());
        self::assertNull($user->getConfirmationToken());

        (new UserService($this->passwordHasher, $this->confirmToken, $this->locales, $this->entityManagerMock, $tokenStorage))
            ->changeEmailAndResetAuthToken($user, $newEmail);

        self::assertEquals($newEmail, $user->getEmail());
        self::assertFalse($user->getIsActive());
        self::assertMatchesRegularExpression('/[a-z0-9]{20,}/', $user->getConfirmationToken());
    }
}
