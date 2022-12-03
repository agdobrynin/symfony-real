<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 *
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function deleteMarkedSoftDeleted(\DateTimeInterface $maxDateTime, ?\DateTimeInterface $minDateTime): int
    {
        $query = $this->createQueryBuilder('c')
            ->delete()
            ->where('c.deleteAt <= :dateMax')
            ->setParameter(':dateMax', $maxDateTime);

        if ($minDateTime) {
            $query = $query->andWhere('c.deleteAt >= :dateMin')
                ->setParameter(':dateMin', $minDateTime);
        }

        return $query->getQuery()->execute();
    }
}
