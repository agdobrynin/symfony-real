<?php

namespace App\Security;

interface ConfirmationTokenGeneratorInterface
{
    public function getRandomSecureToken(int $length): string;
}
