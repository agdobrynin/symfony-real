<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 *
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getCountUnseenNotificationByUser(User $user): int
    {
        $qb = $this->createQueryBuilder('n');

        return $qb->select('count(n)')
            ->where('n.user = :user')
            ->setParameter(':user', $user)
            ->andWhere('n.seen = false')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
