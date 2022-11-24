<?php
declare(strict_types=1);

namespace App\Tests\Integration\Service\MicroPost\User;

use App\Entity\User;
use App\Service\MicroPost\User\UserServiceNewUserInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserServiceNewTest extends KernelTestCase
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
        /** @var UserServiceNewUserInterface $srv */
        $srv = self::getContainer()->get(UserServiceNewUserInterface::class);

        $user = (new User())
            ->setLogin('user')
            ->setEmail('user@domain.com')
            ->setNick('User nick')
            ->setIsActive(true)
            ->setConfirmationToken(null);
        $userLocale = 'ru';
        $password = 'qwerty';

        $srv->addAndSetConfirmationToken($user, $password, $userLocale);

        $this->em->refresh($user);

        self::assertFalse($user->getIsActive());
        self::assertMatchesRegularExpression('/[a-z0-9]{20,}/', $user->getConfirmationToken());
        self::assertEquals($userLocale, $user->getPreferences()->getLocale());
        self::assertTrue($this->hasher->isPasswordValid($user, $password));
    }
}
