<?php

namespace App\Service;

use App\Dto\WelcomeMessageDto;

interface WelcomeMessageInterface
{
    public function welcomeMessage(string $name): WelcomeMessageDto;
}