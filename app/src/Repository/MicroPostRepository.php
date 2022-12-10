<?php
declare(strict_types=1);

namespace App\Repository;

use App\Dto\PaginatorDto;
use App\Entity\MicroPost;
use App\Entity\User;
use App\Repository\Filter\SoftDeleteOnlyFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
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

    /**
     * @return MicroPost[]
     */
    public function findAllByUsersWithPaginator(Collection $users, PaginatorDto $paginatorDto): array
    {
        return $this->createQueryBuilder('mp')
            ->select('mp')
            ->addSelect('c')
            ->leftJoin('mp.comments', 'c')
            ->addSelect('l')
            ->leftJoin('mp.likedBy', 'l')
            ->where('mp.user IN (:following)')
            ->setParameter(':following', $users)
            ->orderBy('mp.date', 'DESC')
            ->setMaxResults($paginatorDto->getPageSize())
            ->setFirstResult($paginatorDto->getFirstResultIndex())
            ->getQuery()
            ->getResult();
    }

    public function getCountByUsers(Collection $users): int
    {
        return (int)$this->createQueryBuilder('mp')
            ->select('count(mp.uuid)')
            ->where('mp.user IN (:following)')
            ->setParameter(':following', $users)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return MicroPost[]
     */
    public function findByUserWithPaginator(User $user, PaginatorDto $paginatorDto): array
    {
        return $this->createQueryBuilder('mp')
            ->select('mp')
            ->where('mp.user = :user')
            ->setParameter(':user', $user)
            ->orderBy('mp.date', 'DESC')
            ->setMaxResults($paginatorDto->getPageSize())
            ->setFirstResult($paginatorDto->getFirstResultIndex())
            ->getQuery()
            ->getResult();
    }

    public function getCountByUser(User $user): int
    {
        return (int)$this->createQueryBuilder('mp')
            ->select('count(mp.uuid)')
            ->where('mp.user = :user')
            ->setParameter(':user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getAllCount(): int
    {
        return (int)$this->createQueryBuilder('mp')
            ->select('count(mp.uuid)')->getQuery()->getSingleScalarResult();
    }

    public function getAllWithPaginatorOrderByDate(PaginatorDto $paginatorDto): array
    {
        return $this->findBy([], ['date' => 'desc'], $paginatorDto->getPageSize(), $paginatorDto->getFirstResultIndex());
    }

    public function getAllWithPaginatorOrderByDeleteAt(PaginatorDto $paginatorDto): array
    {
        if (!$this->getEntityManager()->getFilters()->isEnabled(SoftDeleteOnlyFilter::NAME)) {
            $message = sprintf('Sql filter "%s" not enabled', SoftDeleteOnlyFilter::NAME);

            throw new \LogicException($message);
        }

        return $this->findBy([], ['deleteAt' => 'desc'], $paginatorDto->getPageSize(), $paginatorDto->getFirstResultIndex());
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
}
