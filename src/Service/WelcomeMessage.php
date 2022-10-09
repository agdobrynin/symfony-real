<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

class WelcomeMessage
{
    private const MESSAGES_PREFIX = [
        'Hello dear friend',
        'Hi user',
        'I glad to see you',
    ];

    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function welcomeMessage(string $name): string
    {
        $this->logger->debug('Welcome message start', ['name' => $name]);
        $messagePrefix = self::MESSAGES_PREFIX[array_rand(self::MESSAGES_PREFIX)];
        $message = sprintf('%s %s', $messagePrefix, $name);
        $this->logger->debug('Welcome message complete', ['message' => $message]);

        return $message;
    }
}