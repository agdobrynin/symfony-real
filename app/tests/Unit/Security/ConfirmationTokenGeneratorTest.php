<?php
declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Security\ConfirmationTokenGenerator;
use PHPUnit\Framework\TestCase;

class ConfirmationTokenGeneratorTest extends TestCase
{
    public function testGenerator()
    {
        $lengthOfToken = 40;
        $token = (new ConfirmationTokenGenerator())->getRandomSecureToken($lengthOfToken);
        $this->assertEquals($lengthOfToken, strlen($token));
        $this->assertMatchesRegularExpression('/([a-z0-9]{' . $lengthOfToken . '})/', $token);
    }
}
