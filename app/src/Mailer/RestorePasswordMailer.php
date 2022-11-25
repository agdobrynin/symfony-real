<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Entity\User;
use App\Mailer\Exception\ChangePasswordTokenException;
use App\Service\MicroPost\LocalesInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RestorePasswordMailer implements RestorePasswordMailerInterface
{
    use TemplatedEmailTrait;

    public const TEMPLATE_HTML_PATTERN = 'micro-post/email/restore-password.%s.html.twig';
    public const TEMPLATE_TEXT_PATTERN = 'micro-post/email/restore-password.%s.text.twig';
    public const TEMPLATE_SUBJECT = 'restore_password.email.subject';

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
        if (null === $user->getChangePasswordToken()) {
            throw new ChangePasswordTokenException();
        }

        $email = $this->emailWithUserData(
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
