<?php
declare(strict_types=1);

namespace App\Tests\Unit\Mailer;

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
    private $user;

    public function setUp(): void
    {
        $userPreferences = (new UserPreferences())->setLocale('en');
        $this->user = (new User())
            ->setNick('Superman')
            ->setEmail('superman@outlook.com')
            ->setConfirmationToken('abc-confirm-token')
            ->setPreferences($userPreferences);
    }

    public function testWelcomeMailer(): void
    {
        $adminEmail = 'admin@outlook.com';
        $subject = 'Welcome';
        $templateHtml = sprintf(WelcomeMailer::TEMPLATE_HTML_PATTERN, $this->user->getPreferences()->getLocale());
        $templateText = sprintf(WelcomeMailer::TEMPLATE_TEXT_PATTERN, $this->user->getPreferences()->getLocale());

        $mailer = self::createMock(MailerInterface::class);
        $mailer->expects($this->once())
            ->method('send')
            ->with(self::callback(function (TemplatedEmail $item) use ($subject, $templateText, $templateHtml, $adminEmail) {
                return $item->getSubject() === $subject
                    && $item->getTextTemplate() === $templateText
                    && $item->getHtmlTemplate() === $templateHtml
                    && $item->getFrom()[0]->getAddress() === $adminEmail
                    && $item->getTo()[0]->getAddress() === $this->user->getEmail();
            }));

        $translator = self::createMock(TranslatorInterface::class);
        $translator
            ->expects($this->once())
            ->method("trans")
            ->with('email.registration.subject', [], null, $this->user->getPreferences()->getLocale())
            ->willReturn($subject);

        $locales = self::createMock(LocalesInterface::class);

        $welcomeMailer = new WelcomeMailer($mailer, $adminEmail, $translator, $locales);
        $welcomeMailer->send($this->user);
    }
}
