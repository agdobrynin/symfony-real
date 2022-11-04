<?php

namespace App\Tests\Mailer;

use App\Entity\User;
use App\Entity\UserPreferences;
use App\Mailer\WelcomeMailer;
use App\Service\MicroPost\LocalesInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class WelcomeMailerTest extends TestCase
{
    public function testWelcomeMailer(): void
    {
        $confirmationToken = 'abc-confirm-token';
        $nick = 'Superman';
        $email = 'superman@outlook.com';
        $localeDefault = 'en';
        $adminEmail = 'admin@outlook.com';
        $subject = 'Welcome';
        $templateHtml = sprintf('micro-post/email/welcome.%s.html.twig', $localeDefault);
        $templateText = sprintf('micro-post/email/welcome.%s.text.twig', $localeDefault);

        $userPreferences = (new UserPreferences())->setLocale($localeDefault);
        $user = (new User())
            ->setNick($nick)
            ->setEmail($email)
            ->setConfirmationToken($confirmationToken)
            ->setPreferences($userPreferences);

        $mailer = self::createMock(MailerInterface::class);
        $mailer->expects($this->once())
            ->method('send')
            ->with(self::callback(function (TemplatedEmail $item) use ($subject, $templateText, $templateHtml, $adminEmail, $email) {
                return $item->getSubject() === $subject
                    && $item->getTextTemplate() === $templateText
                    && $item->getHtmlTemplate() === $templateHtml
                    && $item->getFrom()[0]->getAddress() === $adminEmail
                    && $item->getTo()[0]->getAddress() === $email;
            }));

        $translator = self::createMock(TranslatorInterface::class);
        $translator
            ->expects($this->once())
            ->method("trans")
            ->with('email.registration.subject', [], null, $localeDefault)
            ->willReturn($subject);

        $locales = self::createMock(LocalesInterface::class);

        $welcomeMailer = new WelcomeMailer($mailer, $adminEmail, $translator, $locales);
        $welcomeMailer->send($user);
    }
}
