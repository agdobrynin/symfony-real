<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\MicroPost;
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
        if ($minDateTime && $minDateTime > $maxDateTime) {
            $message = sprintf('Date start from %s must be less than date end to %s',
                $minDateTime->format(\DateTimeInterface::ATOM), $maxDateTime->format(\DateTimeInterface::ATOM));

            throw new \LogicException($message);
        }

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

    public function updateDeleteAtByPost(MicroPost $microPost, ?\DateTimeInterface $dateTime): int
    {
        return $this->createQueryBuilder('c')
            ->update()
            ->set('c.deleteAt', ':dateTime')
            ->setParameter(':dateTime', $dateTime)
            ->where('c.post = :post')
            ->setParameter(':post', $microPost)
            ->getQuery()
            ->execute();
    }
}
