<?php

namespace App\Tests\Integration\Service\MicroPost\User;

use App\Entity\User;
use App\Service\MicroPost\User\UserServiceChangeEmailInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserServiceChangeEmailTest extends KernelTestCase
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

        /** @var UserServiceChangeEmailInterface $srv */
        $srv = self::getContainer()->get(UserServiceChangeEmailInterface::class);

        $srv->changeAndResetAuthToken($user, $newEmail);

        $this->em->refresh($user);

        self::assertEquals($newEmail, $user->getEmail());
        self::assertFalse($user->getIsActive());
        self::assertMatchesRegularExpression('/[a-z0-9]{20,}/', $user->getConfirmationToken());
    }
}
