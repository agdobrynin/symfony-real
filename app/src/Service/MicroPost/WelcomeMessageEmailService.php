<?php
declare(strict_types=1);

namespace App\Service\MicroPost;

use App\Entity\User;
use App\Mailer\WelcomeMailerInterface;

class WelcomeMessageEmailService implements WelcomeMessageEmailServiceInterface
{
    private $welcomeMailer;

    public function __construct(WelcomeMailerInterface $welcomeMailer)
    {
        $this->welcomeMailer = $welcomeMailer;
    }

    public function send(User $user, string $locale): bool
    {
        return $this->welcomeMailer->send($user, $locale);
    }
}
