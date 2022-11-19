<?php
declare(strict_types=1);

namespace App\Tests\Unit\Service\MicroPost;

use App\Entity\User;
use App\Mailer\WelcomeMailerInterface;
use App\Service\MicroPost\WelcomeMessageEmailService;
use PHPUnit\Framework\TestCase;

class WelcomeMessageEmailServiceTest extends TestCase
{
    public function testWelcomeMessageEmailService(): void
    {
        $user = new User();
        $welcomeMailer = $this->createMock(WelcomeMailerInterface::class);
        $welcomeMailer->expects(self::once())
            ->method('send')
            ->with($user)
            ->willReturn(true);

        (new WelcomeMessageEmailService($welcomeMailer))->send($user);
    }
}
