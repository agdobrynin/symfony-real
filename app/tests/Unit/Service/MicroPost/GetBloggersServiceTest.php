<?php
declare(strict_types=1);

namespace App\Tests\Unit\Service\MicroPost;

use App\Dto\Exception\PaginatorDtoPageException;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use App\Service\MicroPost\GetBloggersService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PHPUnit\Framework\TestCase;

class GetBloggersServiceTest extends TestCase
{
    public function dataSource(): \Generator
    {
        yield 'Success get bloggers for page 1' => [15, 10, 1, null];
        yield 'Success get bloggers for page 2' => [15, 10, 2, null];
        yield 'Wrong get bloggers for page  more then maximum pages' => [1, 1, 2, PaginatorDtoPageException::class];
        yield 'Success get from empty bloggers array' => [0, 1, 1, null];
        yield 'Wrong get bloggers for page  less then 1' => [0, 10, 0, PaginatorDtoPageException::class];
    }

    /**
     * @dataProvider dataSource
     */
    public function testGetBloggersService(int $total, int $pageSize, int $page, ?string $expectException): void
    {
        $userRepository = self::createMock(UserRepository::class);

        $users = $this->getUserCollection($total);

        $paginator = self::createMock(Paginator::class);
        $paginator->method('getIterator')->willReturn($users);

        $userRepository->expects(self::once())->method('getCountBloggersWithPosts')
            ->willReturn($total);

        $userRepository->expects(self::once())->method('getBloggersWithPostsByPaginator')
            ->with($page, $pageSize)
            ->willReturn($paginator);

        if ($expectException) {
            self::expectException($expectException);
        }

        $commentsRepository = self::createMock(CommentRepository::class);
        //$commentsRepository->expects(self::once())->method('getCountCommentsByUsers');


        $srv = new GetBloggersService($pageSize, $userRepository, $commentsRepository);

        $dto = $srv->getBloggers($page);

        self::assertSame((array)$users, $dto->getBloggers());
        self::assertEquals($page, $dto->getPaginatorDto()->getPage());
        self::assertEquals($pageSize, $dto->getPaginatorDto()->getPageSize());
        self::assertEquals(ceil($total / $pageSize), $dto->getPaginatorDto()->getTotalPages());
    }

    protected function getUserCollection(int $total): \ArrayIterator
    {
        $collection = new \ArrayIterator();

        for ($i = 0; $i < $total; $i++) {
            $collection->append(new User());
        }

        return $collection;
    }
}
