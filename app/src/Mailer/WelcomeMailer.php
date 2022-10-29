<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class WelcomeMailer implements WelcomeMailerInterface
{
    private $mailer;
    private $adminEmail;

    public function __construct(MailerInterface $mailer, string $adminEmail)
    {
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
    }

    public function send(User $user): bool
    {
        $email = (new TemplatedEmail())
            ->from($this->adminEmail)
            ->to($user->getEmail())
            ->subject('Welcome to Micro Post App!')
            ->htmlTemplate('micro-post/email/welcome.html.twig')
            ->textTemplate('micro-post/email/welcome.text.twig')
            ->context(compact('user'));

        $this->mailer->send($email);

        return true;
    }
}
