<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Entity\User;
use App\Service\MicroPost\LocalesInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class WelcomeMailer implements WelcomeMailerInterface
{
    public const TEMPLATE_HTML_PATTERN = 'micro-post/email/welcome.%s.html.twig';
    public const TEMPLATE_TEXT_PATTERN = 'micro-post/email/welcome.%s.text.twig';

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
        $locale = $user->getPreferences()->getLocale() ?: $this->locales->getDefaultLocale();
        $subject = $this->translator->trans('email.registration.subject', [], null, $locale);
        $templateHtml = sprintf(self::TEMPLATE_HTML_PATTERN, $locale);
        $templateText = sprintf(self::TEMPLATE_TEXT_PATTERN, $locale);
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
