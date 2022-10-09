<?php

namespace App\Service;

use App\Dto\WelcomeMessageDto;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class WelcomeMessage implements WelcomeMessageInterface
{
    private const MESSAGES_PREFIX = [
        'Hello dear friend',
        'Hi user',
        'I glad to see you',
    ];

    private $logger;
    private $machineName;
    private $normalizer;

    public function __construct(LoggerInterface $logger, string $machineName, NormalizerInterface $normalizer)
    {
        $this->logger = $logger;
        $this->machineName = $machineName;
        $this->normalizer = $normalizer;
    }

    public function welcomeMessage(string $name): WelcomeMessageDto
    {
        $this->logger->debug('Welcome message start', []);
        $dto = new WelcomeMessageDto();
        $messagePrefix = self::MESSAGES_PREFIX[array_rand(self::MESSAGES_PREFIX)];
        $dto->message = sprintf('%s %s', $messagePrefix, $name);
        $dto->machineName = $this->machineName;
        $data = $this->normalizer->normalize($dto);
        $this->logger->debug('Welcome message complete', $data);

        return $dto;
    }
}