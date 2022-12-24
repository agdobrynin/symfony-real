<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getUsersWhoHaveMoreThen5PostsExcludeUser(User $user): array
    {
        return $this->getUsersWhoHaveMoreThen5PostsQuery()
            ->andHaving('u != :user')
            ->setParameter(':user', $user)
            ->getQuery()
            ->getResult();
    }

    public function getCountBloggersWithPosts(): int
    {
        return
            $this->createQueryBuilder('u')
                ->select('COUNT(DISTINCT u)')
                ->innerJoin('u.posts', 'mp')
                ->getQuery()
                ->getSingleScalarResult();
    }

    public function getBloggersWithPostsByPaginator(int $page, int $pageSize): Paginator
    {
        $query = $this->createQueryBuilder('u')
            ->innerJoin('u.posts', 'mp')
            ->addSelect('mp')
            ->leftJoin('u.followers', 'f')
            ->addSelect('f')
            ->groupBy('u.uuid')
            ->addGroupBy('mp.uuid')
            ->addGroupBy('f.uuid')
            ->orderBy('count(mp)', 'desc')
            ->addOrderBy('u.lastLoginTime', 'desc')
            ->setMaxResults($pageSize)
            ->setFirstResult(($page - 1) * $pageSize);

        return new Paginator($query);
    }

    private function getUsersWhoHaveMoreThen5PostsQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->select('u')
            ->innerJoin('u.posts', 'mp')
            ->groupBy('u.uuid')
            ->having('count(mp) > 5');
    }
}
