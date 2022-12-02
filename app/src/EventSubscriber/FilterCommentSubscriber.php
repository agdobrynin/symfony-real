<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\Filter\SoftDeleteFilter;
use App\Service\MicroPost\SoftDeleteFilterServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FilterCommentSubscriber implements EventSubscriberInterface
{
    private $tokenStorage;
    private $softDeleteFilterService;

    public function __construct(TokenStorageInterface $tokenStorage, SoftDeleteFilterServiceInterface $softDeleteFilterService)
    {
        $this->tokenStorage = $tokenStorage;
        $this->softDeleteFilterService = $softDeleteFilterService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $this->softDeleteFilterService->softDeletedOn();

        if ($this->tokenStorage->getToken()) {
            $user = $this->tokenStorage->getToken()->getUser();
            $isAdminRole = \in_array(User::ROLE_ADMIN, $user->getRoles());
            $disableFilter = !is_null($event->getRequest()->get(SoftDeleteFilter::GET_PARAMETER_SOFT_DELETE_DISABLED));

            if ($disableFilter && $user instanceof User && $isAdminRole) {
                $this->softDeleteFilterService->allOff();
            }
        }
    }
}
