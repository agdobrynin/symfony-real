<?php
declare(strict_types=1);

namespace App\EventListener;

use App\Entity\LikeNotification;
use App\Entity\MicroPost;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;

class LikeNotificationSubscriber implements EventSubscriberInterface
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        /** @var \Doctrine\ORM\PersistentCollection $collectionUpdate */
        foreach ($uow->getScheduledCollectionUpdates() as $collectionUpdate) {
            /** @var MicroPost|mixed $entity */
            $entity = $collectionUpdate->getOwner();
            if ($entity instanceof MicroPost) {
                if ('likedBy' === $collectionUpdate->getMapping()['fieldName']) {
                    if ($insertDiff = $collectionUpdate->getInsertDiff()) {
                        $likeNotification = new LikeNotification();
                        $likeNotification->setUser($entity->getUser());
                        $likeNotification->setPost($entity);
                        $likeNotification->setLikedBy(reset($insertDiff));
                        $em->persist($likeNotification);
                        $uow->computeChangeSet(
                            $em->getClassMetadata(LikeNotification::class),
                            $likeNotification
                        );
                    }
                }
            }
        }
    }
}
