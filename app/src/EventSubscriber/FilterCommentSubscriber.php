<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\Filter\SoftDeleteFilter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FilterCommentSubscriber implements EventSubscriberInterface
{
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
            RequestEvent::class => 'onRequest'
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        $this->em->getFilters()->enable(SoftDeleteFilter::NAME);

        if ($this->tokenStorage->getToken()) {
            $user = $this->tokenStorage->getToken()->getUser();

            if ($user instanceof User && \in_array(User::ROLE_ADMIN, $user->getRoles())) {
                $this->em->getFilters()->disable(SoftDeleteFilter::NAME);
            }
        }
    }
}
