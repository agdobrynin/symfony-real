<?php

namespace App\Security;

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
