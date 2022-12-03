<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Comment;
use App\Entity\MicroPost;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;

class SoftDeleteMicroPostSubscriber implements EventSubscriberInterface
{
    public function getSubscribedEvents(): array
    {
        return [Events::preFlush => 'preFlush', 500];
    }

    public function preFlush(PreFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityDeletions() as $entityDeletion) {
            if ($entityDeletion instanceof MicroPost) {
                $deleteDate = $entityDeletion->getDeleteAt();
                $currentDate = new \DateTime();

                if (null === $deleteDate || $deleteDate >= $currentDate) {
                    $entityDeletion->setDeleteAt($currentDate);
                    $em->persist($entityDeletion);
                    $em->getRepository(Comment::class)
                        ->updateDeleteAtByPost($entityDeletion, $currentDate);
                }
            }
        }
    }
}
