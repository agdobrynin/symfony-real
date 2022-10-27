<?php

namespace App\Repository;

use App\Entity\UnlikeNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UnlikeNotification>
 *
 * @method UnlikeNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method UnlikeNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method UnlikeNotification[]    findAll()
 * @method UnlikeNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UnlikeNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UnlikeNotification::class);
    }
}
