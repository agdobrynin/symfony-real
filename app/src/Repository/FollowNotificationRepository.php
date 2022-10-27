<?php

namespace App\Repository;

use App\Entity\FollowNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FollowNotification>
 *
 * @method FollowNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method FollowNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method FollowNotification[]    findAll()
 * @method FollowNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FollowNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FollowNotification::class);
    }
}
