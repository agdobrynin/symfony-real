<?php
declare(strict_types=1);

namespace App\Tests\Integration\Service\MicroPost\User;

use App\Entity\User;
use App\Service\MicroPost\User\UserServiceRestorePasswordTokenInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserServiceRestorePasswordTokenTest extends KernelTestCase
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

    public function testService(): void
    {
        $user = $this->em->getRepository(User::class)
            ->findOneBy(['changePasswordToken' => null, 'isActive' => true]);

        self::assertTrue($user->getIsActive());
        self::assertNull($user->getChangePasswordToken());

        /** @var UserServiceRestorePasswordTokenInterface $srv */
        $srv = self::getContainer()->get(UserServiceRestorePasswordTokenInterface::class);
        $srv->refreshAndUnsetAuthToken($user);

        self::assertTrue($user->getIsActive());
        self::assertMatchesRegularExpression('/([0-9a-z]{20,40})/', $user->getChangePasswordToken());
    }
}
