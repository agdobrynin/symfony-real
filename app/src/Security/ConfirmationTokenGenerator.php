<?php
declare(strict_types=1);

namespace App\Security;

class ConfirmationTokenGenerator implements ConfirmationTokenGeneratorInterface
{
    public function getRandomSecureToken(int $length = 40): string
    {
        return bin2hex(random_bytes($length / 2));
    }
}
