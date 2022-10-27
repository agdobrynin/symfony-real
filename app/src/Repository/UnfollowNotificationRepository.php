<?php

namespace App\Repository;

use App\Entity\UnfollowNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UnfollowNotification>
 *
 * @method UnfollowNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method UnfollowNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method UnfollowNotification[]    findAll()
 * @method UnfollowNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UnfollowNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UnfollowNotification::class);
    }
}
