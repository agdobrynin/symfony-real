<?php
declare(strict_types=1);

namespace App\Tests\Unit\Mailer;

use App\Entity\User;
use App\Entity\UserPreferences;
use App\Mailer\Exception\ChangePasswordTokenException;
use App\Mailer\RestorePasswordMailer;
use App\Service\MicroPost\LocalesInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RestorePasswordMailerTest extends TestCase
{
    public function getSourceData(): \Generator
    {
        yield 'success' => [
            (new User())->setLogin('user')
                ->setEmail('user@domain.com')
                ->setPreferences((new UserPreferences())->setLocale('en'))
                ->setChangePasswordToken('abc-abc'),
            null,
        ];

        yield 'fail' => [
            (new User())->setLogin('user')
                ->setEmail('user@domain.com')
                ->setPreferences((new UserPreferences())->setLocale('en'))
                ->setChangePasswordToken(null),
            ChangePasswordTokenException::class,
        ];
    }

    /**
     * @dataProvider getSourceData
     */
    public function testMailer(User $user, ?string $expectException): void
    {
        $adminEmail = 'admin@domain.com';
        $emailSubject = 'test subject';
        $templateText = sprintf(RestorePasswordMailer::TEMPLATE_TEXT_PATTERN, $user->getPreferences()->getLocale());
        $templateHtml = sprintf(RestorePasswordMailer::TEMPLATE_HTML_PATTERN, $user->getPreferences()->getLocale());

        $expects = $expectException ? self::never() : self::atLeastOnce();

        $mailer = self::createMock(MailerInterface::class);
        $mailer->expects($expects)->method('send')
            ->with(self::callback(static function (TemplatedEmail $item) use ($emailSubject, $templateText, $templateHtml, $adminEmail, $user) {
                return $item->getSubject() === $emailSubject
                    && $item->getTextTemplate() === $templateText
                    && $item->getHtmlTemplate() === $templateHtml
                    && $item->getFrom()[0]->getAddress() === $adminEmail
                    && $item->getTo()[0]->getAddress() === $user->getEmail();
            }));

        $translator = self::createMock(TranslatorInterface::class);
        $translator
            ->expects($expects)
            ->method('trans')
            ->with(RestorePasswordMailer::TEMPLATE_SUBJECT, [], null, $user->getPreferences()->getLocale())
            ->willReturn($emailSubject);

        $locales = self::createMock(LocalesInterface::class);

        if ($expectException) {
            self::expectException($expectException);
        }

        (new RestorePasswordMailer($mailer, $adminEmail, $translator, $locales))->send($user);
    }
}
