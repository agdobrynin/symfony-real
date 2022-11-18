<?php
declare(strict_types=1);

namespace App\Twig;

use App\Entity\FollowNotification;
use App\Entity\LikeNotification;
use App\Entity\UnfollowNotification;
use App\Entity\UnlikeNotification;
use App\Entity\User;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigTest;

class AppExtension extends AbstractExtension
{
    public function getTests(): array
    {
        return [
            new TwigTest('is_notification_like', [$this, 'isNotificationLike']),
            new TwigTest('is_notification_unlike', [$this, 'isNotificationUnlike']),
            new TwigTest('is_notification_follow', [$this, 'isNotificationFollow']),
            new TwigTest('is_notification_unfollow', [$this, 'isNotificationUnfollow']),
            new TwigTest('is_user', [$this, 'isUser'])
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('text_by_percent', [$this, 'textByPercent']),
        ];
    }

    public function textByPercent(string $text, int $percent = 50, int $minLength = 50): string
    {
        if ($percent > 1 && $percent <= 100) {
            $lenSource = mb_strlen($text);

            if ($lenSource < $minLength) {
                $lenCut = $lenSource;
            } else {
                $lenCut = (int)($lenSource * $percent / 100);

                if ($lenCut < $minLength) {
                    $lenCut = $minLength;
                }
            }

            return mb_substr($text, 0, $lenCut);
        }

        $message = sprintf('Percent value of part text must be between 1 and 100. Yor values is %s', $percent);

        throw new \UnexpectedValueException($message);
    }

    public function isNotificationLike($var): bool
    {
        return $var instanceof LikeNotification;
    }

    public function isNotificationUnlike($var): bool
    {
        return $var instanceof UnlikeNotification;
    }

    public function isNotificationFollow($var): bool
    {
        return $var instanceof FollowNotification;
    }

    public function isNotificationUnfollow($var): bool
    {
        return $var instanceof UnfollowNotification;
    }

    public function isUser($var): bool
    {
        return $var instanceof User;
    }

}
