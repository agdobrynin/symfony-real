<?php

namespace App\Repository;

use App\Entity\ByUserNotification;
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
}
