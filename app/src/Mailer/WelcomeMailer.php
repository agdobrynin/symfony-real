<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class WelcomeMailer implements WelcomeMailerInterface
{
    private $mailer;
    private $adminEmail;
    private $translator;

    public function __construct(MailerInterface $mailer, string $adminEmail, TranslatorInterface $translator)
    {
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
        $this->translator = $translator;
    }

    public function send(User $user, string $locale): bool
    {
        $subject = $this->translator->trans('email.registration.subject');
        $templateHtml = sprintf('micro-post/email/welcome.%s.html.twig', $locale);
        $templateText = sprintf('micro-post/email/welcome.%s.text.twig', $locale);;
        $email = (new TemplatedEmail())
            ->from($this->adminEmail)
            ->to($user->getEmail())
            ->subject($subject)
            ->htmlTemplate($templateHtml)
            ->textTemplate($templateText)
            ->context(compact('user', 'locale'));

        $this->mailer->send($email);

        return true;
    }
}
