<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Entity\User;
use App\Service\MicroPost\LocalesInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmailChangeMailer implements EmailChangeMailerInterface
{
    use TemplatedEmailTrait;

    public const TEMPLATE_HTML_PATTERN = 'micro-post/email/change-email.%s.html.twig';
    public const TEMPLATE_TEXT_PATTERN = 'micro-post/email/change-email.%s.text.twig';
    public const TEMPLATE_SUBJECT = 'email.change_email.subject';

    private $mailer;
    private $adminEmail;
    private $translator;
    private $locales;

    public function __construct(MailerInterface $mailer, string $adminEmail, TranslatorInterface $translator, LocalesInterface $locales)
    {
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
        $this->translator = $translator;
        $this->locales = $locales;
    }

    public function send(User $user): bool
    {
        $email = $this->emailWithConfirmationUser(
            $user,
            $this->locales,
            $this->adminEmail,
            self::TEMPLATE_HTML_PATTERN,
            self::TEMPLATE_TEXT_PATTERN,
            self::TEMPLATE_SUBJECT
        );

        $this->mailer->send($email);

        return true;
    }
}
