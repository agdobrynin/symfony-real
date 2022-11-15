<?php
declare(strict_types=1);

namespace App\Repository;

use App\Dto\PaginatorDto;
use App\Entity\MicroPost;
use App\Entity\User;
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

    public function getAllWithPaginator(PaginatorDto $paginatorDto): array
    {

        return $this->findBy([], ['date' => 'desc'], $paginatorDto->getPageSize(), $paginatorDto->getFirstResultIndex());
    }
}
