<?php

namespace App\Repository;

use App\Entity\LikeUnlikeNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @codeCoverageIgnore
 * @extends ServiceEntityRepository<LikeUnlikeNotification>
 *
 * @method LikeUnlikeNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method LikeUnlikeNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method LikeUnlikeNotification[]    findAll()
 * @method LikeUnlikeNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LikeUnlikeNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LikeUnlikeNotification::class);
    }
}
