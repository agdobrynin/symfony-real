<?php
declare(strict_types=1);

namespace App\Tests\Integration\Mailer;

use App\Entity\User;
use App\Entity\UserPreferences;
use App\Mailer\Exception\ChangePasswordTokenException;
use App\Mailer\RestorePasswordMailerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;

class RestorePasswordMailerTest extends KernelTestCase
{
    use MailerAssertionsTrait;

    public function getDataSource(): \Generator
    {
        yield 'success send email with link for restore password' => [
            (new User())->setLogin('user')
                ->setNick('User nick')
                ->setEmail('user@domain.com')
                ->setPreferences((new UserPreferences())->setLocale('en'))
                ->setChangePasswordToken('abc-abc'),
            null,
        ];

        yield 'fail send email for restore password because change password token is null' => [
            (new User())->setLogin('user')
                ->setEmail('user@domain.com')
                ->setPreferences((new UserPreferences())->setLocale('en'))
                ->setChangePasswordToken(null),
            ChangePasswordTokenException::class,
        ];
    }

    /**
     * @dataProvider getDataSource
     */
    public function testMailer(User $user, ?string $expectException): void
    {
        /** @var RestorePasswordMailerInterface $mailer */
        $mailer = self::getContainer()->get(RestorePasswordMailerInterface::class);

        if ($expectException) {
            self::expectException($expectException);
        }

        $mailer->send($user);


        $email = self::getMailerMessage();

        $linkToChangePassword = sprintf('/micro-post/%s/restore/password/confirm/%s', $user->getPreferences()->getLocale(), $user->getChangePasswordToken());
        self::assertEmailHtmlBodyContains($email, $linkToChangePassword);
        self::assertEmailTextBodyContains($email, $linkToChangePassword);

        self::assertEmailHtmlBodyContains($email, $user->getNick());
        self::assertEmailHtmlBodyContains($email, $user->getLogin());
        self::assertEmailTextBodyContains($email, $user->getNick());
        self::assertEmailTextBodyContains($email, $user->getLogin());

        $emailAsString = $email->toString();
        $headerMailToForRegExp = '/To:.*' . preg_quote($user->getEmail()) . '/i';
        self::assertMatchesRegularExpression($headerMailToForRegExp, $emailAsString);

        $adminEmail = self::getContainer()->getParameter('micropost.admin.email');
        $headerMailFromForRegExp = '/From:.*' . preg_quote($adminEmail) . '/i';
        self::assertMatchesRegularExpression($headerMailFromForRegExp, $emailAsString);
    }
}
