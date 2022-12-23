<?php

namespace App\Repository;

use App\Entity\LikeUnlikeNotification;
use App\Entity\User;
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

    public function getUnseenByUser(User $user)
    {
        $query = $this->createQueryBuilder('lun')
            ->innerJoin('lun.byUser', 'by_user')
            ->addSelect('by_user')
            ->leftJoin('lun.post', 'post')
            ->addSelect('post')
            ->where('lun.user = :user')
            ->setParameter(':user', $user)
            ->andWhere('lun.seen = :seen')
            ->setParameter(':seen', false);

        return $query->getQuery()->getResult();
    }
}
