<?php
declare(strict_types=1);

namespace App\Twig;

use App\Entity\FollowNotification;
use App\Entity\LikeNotification;
use App\Entity\UnfollowNotification;
use App\Entity\UnlikeNotification;
use App\Entity\User;
use App\EventSubscriber\FilterCommentSubscriber;
use App\Security\Exception\LoginNotConfirmAccountStatusException;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

class AppExtension extends AbstractExtension
{
    private $router;
    private $requestStack;

    public function __construct(RouterInterface $router, RequestStack $requestStack)
    {
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTests(): array
    {
        return [
            new TwigTest('is_notification_like', [$this, 'isNotificationLike']),
            new TwigTest('is_notification_unlike', [$this, 'isNotificationUnlike']),
            new TwigTest('is_notification_follow', [$this, 'isNotificationFollow']),
            new TwigTest('is_notification_unfollow', [$this, 'isNotificationUnfollow']),
            new TwigTest('is_user', [$this, 'isUser']),
            new TwigTest('is_security_login_not_confirm', [$this, 'isSecurityLoginNotConfirm']),
            new TwigTest('is_security_bad_credentials', [$this, 'isSecurityBadCredentials']),
        ];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('text_by_percent', [$this, 'textByPercent']),
        ];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('user_with_link_to_user_page', [$this, 'userNickWithLinkToUserPage']),
            new TwigFunction('current_url_switch_soft_deleted', [$this, 'currentUrlSwitchSoftDeleted']),
            new TwigFunction('current_url_append_params', [$this, 'currentUrlAppendParams']),
            new TwigFunction('has_soft_deleted_param', [$this, 'hasSoftDeletedParam']),
        ];
    }

    public function userNickWithLinkToUserPage(User $user, ?string $locale = null): string
    {
        $dataForRoute = ['uuid' => $user->getUuid()];

        if ($locale) {
            $dataForRoute['_locale'] = $locale;
        }

        $pathToUserPage = $this->router->generate('micro_post_by_user', $dataForRoute);

        return sprintf('%s@<a href="%s">%s</a>', $user->getEmoji(), $pathToUserPage, $user->getNick());
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

    public function isSecurityLoginNotConfirm($var): bool
    {
        return $var instanceof LoginNotConfirmAccountStatusException;
    }

    public function isSecurityBadCredentials($var): bool
    {
        return $var instanceof BadCredentialsException;
    }

    public function hasSoftDeletedParam(InputBag $inputBag): bool
    {
        return $inputBag->has(FilterCommentSubscriber::GET_PARAMETER_SOFT_DELETE_DISABLED);
    }

    public function currentUrlSwitchSoftDeleted(bool $enabled = false): string
    {
        $request = $this->requestStack->getCurrentRequest();

        $currentRoute = $request->get('_route');
        $routeParams = $request->get('_route_params');
        $query = $request->query->all();

        if ($enabled) {
            $query[FilterCommentSubscriber::GET_PARAMETER_SOFT_DELETE_DISABLED] = 1;
        } else {
            unset($query[FilterCommentSubscriber::GET_PARAMETER_SOFT_DELETE_DISABLED]);
        }

        return $this->router->generate($currentRoute, array_merge($routeParams, $query));
    }

    public function currentUrlAppendParams(array $params): string
    {
        $request = $this->requestStack->getCurrentRequest();

        $currentRoute = $request->get('_route');
        $routeParams = $request->get('_route_params');

        return $this->router->generate($currentRoute, array_merge($routeParams, $request->query->all(), $params));
    }
}
