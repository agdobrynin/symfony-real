<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Entity\User;
use App\Service\MicroPost\LocalesInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

trait TemplatedEmailTrait
{
    protected function emailWithConfirmationUser(
        User             $user,
        LocalesInterface $locales,
        string           $mailFrom,
        string           $htmlTemplatePattern,
        string           $textTemplatePattern,
        string           $subject
    ): TemplatedEmail
    {
        $locale = $user->getPreferences()->getLocale() ?: $locales->getDefaultLocale();
        $templateHtml = sprintf($htmlTemplatePattern, $locale);
        $templateText = sprintf($textTemplatePattern, $locale);
        $subject = $this->translator->trans($subject, [], null, $locale);

        return (new TemplatedEmail())
            ->from($mailFrom)
            ->to($user->getEmail())
            ->subject($subject)
            ->htmlTemplate($templateHtml)
            ->textTemplate($templateText)
            ->context(compact('user', 'locale'));
    }
}
