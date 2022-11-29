<?php
declare(strict_types=1);

namespace App\Repository;

use App\Dto\PaginatorDto;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    /**
     * @return User[]
     */
    public function getBloggersWithPostsByPaginator(PaginatorDto $paginatorDto): array
    {
        return $this->createQueryBuilder('u')
            ->select('u')
            ->innerJoin('u.posts', 'mp')
            ->groupBy('u.uuid')
            ->orderBy('count(mp)', 'desc')
            ->addOrderBy('u.lastLoginTime', 'desc')
            ->setMaxResults($paginatorDto->getPageSize())
            ->setFirstResult($paginatorDto->getFirstResultIndex())
            ->getQuery()
            ->getResult();
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
