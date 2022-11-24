<?php
declare(strict_types=1);

namespace App\Tests\Integration\Twig;

use App\Entity\FollowNotification;
use App\Entity\LikeNotification;
use App\Entity\UnfollowNotification;
use App\Entity\UnlikeNotification;
use App\Entity\User;
use App\Security\Exception\LoginNotConfirmAccountStatusException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;
use Twig\Error\RuntimeError;

class AppExtensionTest extends KernelTestCase
{
    /**
     * @var Environment
     */
    private $twig;

    protected function setUp(): void
    {
        parent::setUp();
        $this->twig = self::getContainer()->get(Environment::class);
    }

    /**
     * @dataProvider dataProviderForFunction
     */
    public function testFunction(array $param, string $template, string $expectValue, ?string $expectException = null): void
    {
        $template = $this->twig->createTemplate($template);

        if ($expectException) {
            self::expectException($expectException);
        }

        self::assertEquals($expectValue, $this->twig->render($template, $param));
    }

    public function dataProviderForFunction(): \Generator
    {
        yield 'Test function is_notification_like for class LikeNotification' => [
            ['notification' => new LikeNotification()],
            '{%if notification is is_notification_like %}OK{%endif%}',
            'OK',
            null,
        ];

        yield 'Test function is_notification_unlike for class UnlikeNotification' => [
            ['notification' => new UnlikeNotification()],
            '{%if notification is is_notification_unlike %}OK{%endif%}',
            'OK'
        ];

        yield 'Test function is_notification_follow for class FollowNotification' => [
            ['notification' => new FollowNotification()],
            '{%if notification is is_notification_follow %}OK{%endif%}',
            'OK',
            null,
        ];

        yield 'Test function is_notification_unfollow for class UnfollowNotification' => [
            ['notification' => new UnfollowNotification()],
            '{%if notification is is_notification_unfollow %}OK{%endif%}',
            'OK',
            null,
        ];

        yield 'Test function is_notification_unfollow for class FollowNotification' => [
            ['notification' => new FollowNotification()],
            '{%if notification is is_notification_unfollow %}OK{%else%}Hmmm{%endif%}',
            'Hmmm',
            null,
        ];

        $user = (new User())->setEmoji('😎')->setNick('Superman');

        yield 'Twig function user_with_link_to_user_page is success' => [
            ['user' => $user],
            '{{ user_with_link_to_user_page(user, "ru")|raw }}',
            '😎@<a href="/micro-post/ru/user/' . $user->getUuid() . '">Superman</a>',
            null,
        ];

        yield 'Twig filter text_by_percent is success test 1' => [
            [],
            '{{ "Lorem ipsum dolor sit amet"|text_by_percent(50,100) }}',
            'Lorem ipsum dolor sit amet',
            null,
        ];

        yield 'Twig filter text_by_percent is success test 2' => [
            [],
            '{{ "Loremipsum"|text_by_percent(50, 2) }}',
            'Lorem',
            null,
        ];

        yield 'Twig filter text_by_percent is success test 3' => [
            [],
            '{{ "Loremipsum"|text_by_percent(50, 9) }}',
            'Loremipsu',
            null,
        ];

        yield 'Twig filter text_by_percent is fail test 1' => [
            [],
            '{{ "Loremipsum"|text_by_percent(150, 2) }}',
            'Lorem',
            RuntimeError::class
        ];

        yield 'Twig test is_security_login_not_confirm for class LoginNotConfirmAccountStatusException' => [
            ['error' => new LoginNotConfirmAccountStatusException()],
            '{%if error is is_security_login_not_confirm %}OK{%endif%}',
            'OK'
        ];

        yield 'Twig test is_security_login_not_confirm for class LogicException' => [
            ['error' => new \LogicException()],
            '{%if error is is_security_login_not_confirm %}OK{%endif%}',
            ''
        ];
    }
}
