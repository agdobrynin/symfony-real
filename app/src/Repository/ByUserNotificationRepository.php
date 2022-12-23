<?php

namespace App\Repository;

use App\Entity\ByUserNotification;
use App\Entity\FollowNotification;
use App\Entity\UnfollowNotification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ByUserNotification>
 *
 * @method ByUserNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method ByUserNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method ByUserNotification[]    findAll()
 * @method ByUserNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ByUserNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ByUserNotification::class);
    }

    public function getUnseenFollowUnfollowNotifyForUser(User $user)
    {
        $query = $this->createQueryBuilder('bun')
            ->innerJoin('bun.byUser', 'byUser')
            ->addSelect('byUser')
            ->where('bun.user = :user')
            ->setParameter(':user', $user)
            ->andWhere('bun.seen = :seen')
            ->setParameter(':seen', false)
            ->andWhere('bun INSTANCE OF ' . FollowNotification::class)
            ->orWhere('bun INSTANCE OF ' . UnfollowNotification::class);

        return $query->getQuery()->getResult();
    }
}
