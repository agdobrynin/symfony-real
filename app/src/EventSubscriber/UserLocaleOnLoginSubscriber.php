<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use App\Service\MicroPost\LocalesInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class UserLocaleOnLoginSubscriber implements EventSubscriberInterface
{
    private $requestStack;
    private $locales;

    public function __construct(RequestStack $requestStack, LocalesInterface $locales)
    {
        $this->requestStack = $requestStack;
        $this->locales = $locales;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin'
        ];
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof User) {
            $userLocale = $user->getPreferences()->getLocale() ?: $this->locales->getDefaultLocale();
            $this->requestStack->getCurrentRequest()->setLocale($userLocale);
        }
    }
}
