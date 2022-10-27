<?php
declare(strict_types=1);

namespace App\EventListener;

use App\Entity\FollowNotification;
use App\Entity\UnfollowNotification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;

class FollowerNotificationSubscriber implements EventSubscriberInterface
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
            /** @var User|mixed $entity */
            $entity = $collectionUpdate->getOwner();
            $isUserEntity = $entity instanceof User;
            $hasFieldNameFollowing = User::FIELD_NAME_FOR_NOTIFICATION_FOLLOW === $collectionUpdate->getMapping()['fieldName'];

            if (!$isUserEntity && !$hasFieldNameFollowing) {
                continue;
            }

            if ($insertDiff = $collectionUpdate->getInsertDiff()) {
                $notification = (new FollowNotification())
                    ->setUser(reset($insertDiff))
                    ->setByUser($entity);

                $em->persist($notification);
                $uow->computeChangeSet(
                    $em->getClassMetadata(FollowNotification::class),
                    $notification
                );
            }

            if ($deleteDiff = $collectionUpdate->getDeleteDiff()) {
                $notification = (new UnfollowNotification())
                    ->setUser(reset($deleteDiff))
                    ->setByUser($entity);

                $em->persist($notification);
                $uow->computeChangeSet(
                    $em->getClassMetadata(UnfollowNotification::class),
                    $notification
                );
            }
        }
    }
}
