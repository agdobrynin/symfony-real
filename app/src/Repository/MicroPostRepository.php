<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\MicroPost;
use App\Entity\User;
use App\Repository\Filter\SoftDeleteOnlyFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MicroPost>
 *
 * @method MicroPost|null find($id, $lockMode = null, $lockVersion = null)
 * @method MicroPost|null findOneBy(array $criteria, array $orderBy = null)
 * @method MicroPost[]    findAll()
 * @method MicroPost[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MicroPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MicroPost::class);
    }

    public function add(MicroPost $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MicroPost $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findFollowingMicroPostWithPaginator(User $user, int $page, int $pageSize): Paginator
    {
        $query = $this->microPostWithAllData($page, $pageSize)
            ->innerJoin('author.followers', 'followersPosts')
            ->where('followersPosts IN (:user)')
            ->setParameter(':user', $user)
            ->orderBy('mp.date', 'DESC');

        return new Paginator($query);
    }

    public function findMicroPostByUserWithPaginator(User $user, int $page, int $pageSize): Paginator
    {
        $query = $this->microPostWithAllData($page, $pageSize)
            ->where('author = :user')
            ->setParameter(':user', $user)
            ->orderBy('mp.date', 'DESC');

        return new Paginator($query);
    }

    public function getAllWithPaginator(int $page, int $pageSize): Paginator
    {
        return new Paginator($this->microPostWithAllData($page, $pageSize));
    }

    public function getAllWithPaginatorOrderByDeleteAt(int $page, int $pageSize): Paginator
    {
        if (!$this->getEntityManager()->getFilters()->isEnabled(SoftDeleteOnlyFilter::NAME)) {
            $message = sprintf('Sql filter "%s" not enabled', SoftDeleteOnlyFilter::NAME);

            throw new \LogicException($message);
        }

        $query = $this->microPostWithAllData($page, $pageSize)->orderBy('mp.deleteAt', 'DESC');

        return new Paginator($query);
    }

    public function getCountBloggersWithPosts()
    {
        $qb = $this->createQueryBuilder('mp');

        return $qb->select($qb->expr()->countDistinct('mp.user'))
            ->getQuery()->getSingleScalarResult();
    }

    public function deleteMarkedSoftDeleted(\DateTimeInterface $maxDateTime, ?\DateTimeInterface $minDateTime): int
    {
        if ($minDateTime && $minDateTime > $maxDateTime) {
            $message = sprintf('Date start from %s must be less than date end to %s',
                $minDateTime->format(\DateTimeInterface::ATOM), $maxDateTime->format(\DateTimeInterface::ATOM));

            throw new \LogicException($message);
        }

        $query = $this->createQueryBuilder('mp')
            ->delete()
            ->where('mp.deleteAt <= :dateMax')
            ->setParameter(':dateMax', $maxDateTime);

        if ($minDateTime) {
            $query = $query->andWhere('mp.deleteAt >= :dateMin')
                ->setParameter(':dateMin', $minDateTime);
        }

        return $query->getQuery()->execute();
    }

    private function microPostWithAllData(int $page, int $pageSize): QueryBuilder
    {
        return $this->createQueryBuilder('mp')
            ->leftJoin('mp.user', 'author')
            ->addSelect('author')
            ->leftJoin('mp.comments', 'userComments')
            ->addSelect('userComments')
            ->leftJoin('mp.likedBy', 'likeByUser')
            ->addSelect('likeByUser')
            ->setFirstResult(($page - 1) * $pageSize)
            ->setMaxResults($pageSize)
            ->orderBy('mp.date', 'DESC');
    }
}
