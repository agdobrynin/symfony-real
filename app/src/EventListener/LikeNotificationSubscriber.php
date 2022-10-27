<?php
declare(strict_types=1);

namespace App\EventListener;

use App\Entity\LikeNotification;
use App\Entity\MicroPost;
use App\Entity\UnlikeNotification;
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
            $isMicropostEntity = $entity instanceof MicroPost;
            $hasFieldNameLikedBy = 'likedBy' === $collectionUpdate->getMapping()['fieldName'];

            if (!$isMicropostEntity && !$hasFieldNameLikedBy) {
                continue;
            }

            $likeDiff = $collectionUpdate->getInsertDiff();
            $unlikeDiff = $collectionUpdate->getDeleteDiff();

            if ($likeDiff) {
                $notification = (new LikeNotification())
                    ->setUser($entity->getUser())
                    ->setPost($entity)
                    ->setByUser(reset($likeDiff));

                $em->persist($notification);
                $uow->computeChangeSet(
                    $em->getClassMetadata(LikeNotification::class),
                    $notification
                );
            }

            if ($unlikeDiff) {
                $notification = (new UnlikeNotification())
                    ->setUser($entity->getUser())
                    ->setPost($entity)
                    ->setByUser(reset($unlikeDiff));

                $em->persist($notification);
                $uow->computeChangeSet(
                    $em->getClassMetadata(UnlikeNotification::class),
                    $notification
                );
            }
        }
    }
}
