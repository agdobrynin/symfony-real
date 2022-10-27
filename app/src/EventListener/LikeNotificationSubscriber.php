<?php
declare(strict_types=1);

namespace App\EventListener;

use App\Entity\LikeNotification;
use App\Entity\LikeUnlikeNotification;
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
            $hasFieldNameLikedBy = MicroPost::FIELD_NAME_FOR_NOTIFICATION_LIKED === $collectionUpdate->getMapping()['fieldName'];

            if (!$isMicropostEntity && !$hasFieldNameLikedBy) {
                continue;
            }

            if ($insertDiff = $collectionUpdate->getInsertDiff()) {
                $notification = (new LikeNotification())
                    ->setUser($entity->getUser())
                    ->setPost($entity)
                    ->setByUser(reset($insertDiff));

                $em->persist($notification);
                $uow->computeChangeSet(
                    $em->getClassMetadata(LikeNotification::class),
                    $notification
                );
            }

            if ($deleteDiff = $collectionUpdate->getDeleteDiff()) {
                $notification = (new UnlikeNotification())
                    ->setUser($entity->getUser())
                    ->setPost($entity)
                    ->setByUser(reset($deleteDiff));

                $em->persist($notification);
                $uow->computeChangeSet(
                    $em->getClassMetadata(UnlikeNotification::class),
                    $notification
                );
            }
        }

        // Delete from notification when delete MicroPost
        $postForDelete = [];

        foreach ($uow->getScheduledEntityDeletions() as $entityDeletion) {
            if ($entityDeletion instanceof MicroPost) {
                $postForDelete[] = $entityDeletion;
            }
        }

        if ($postForDelete) {
            $em->createQueryBuilder()
                ->delete(LikeUnlikeNotification::class, 'n')
                ->where('n.post IN (:posts)')
                ->setParameter(':posts', $postForDelete)
                ->getQuery()
                ->execute();
        }
    }
}
