<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\Filter\SoftDeleteFilter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FilterCommentSubscriber implements EventSubscriberInterface
{
    public const GET_PARAMETER_SOFT_DELETE_DISABLED = 'with-soft-deleted';

    private $tokenStorage;
    private $em;

    public function __construct(TokenStorageInterface $tokenStorage, EntityManagerInterface $em)
    {
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $this->em->getFilters()->enable(SoftDeleteFilter::NAME);

        if ($this->tokenStorage->getToken()) {
            $user = $this->tokenStorage->getToken()->getUser();
            $isAdminRole = \in_array(User::ROLE_ADMIN, $user->getRoles());
            $disableFilter = !is_null($event->getRequest()->get(self::GET_PARAMETER_SOFT_DELETE_DISABLED));

            if ($disableFilter && $user instanceof User && $isAdminRole) {
                $this->em->getFilters()->disable(SoftDeleteFilter::NAME);
            }
        }
    }
}
