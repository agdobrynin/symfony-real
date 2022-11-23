<?php
declare(strict_types=1);

namespace App\Tests\Unit\Service\MicroPost;

use App\Dto\Exception\PaginatorDtoPageException;
use App\Entity\User;
use App\Repository\MicroPostRepository;
use App\Repository\UserRepository;
use App\Service\MicroPost\GetBloggersService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class GetBloggersServiceTest extends TestCase
{
    public function dataSource(): \Generator
    {
        yield [15, 10, 1, null];
        yield [15, 10, 2, null];
        yield [1, 1, 2, PaginatorDtoPageException::class];
        yield [0, 1, 1, null];
        yield [0, 10, 0, PaginatorDtoPageException::class];
    }

    /**
     * @dataProvider dataSource
     */
    public function testGetBloggersService(int $total, int $pageSize, int $page, ?string $expectException): void
    {
        $userRepository = self::createMock(UserRepository::class);
        $microPostRepository = self::createMock(MicroPostRepository::class);

        $microPostRepository->expects(self::once())
            ->method('getCountBloggersWithPosts')
            ->willReturn($total);

        $userCollection = $this->getUserCollection($total);
        $slicedUserCollection = [];

        if ($expectException) {
            self::expectException($expectException);
        } else {
            $firstIndex = ($page - 1) * $pageSize;
            $slicedUserCollection = array_values($userCollection->slice($firstIndex, $pageSize));

            $userRepository->expects(self::once())->method('getBloggersWithPostsByPaginator')
                ->willReturn($slicedUserCollection);
        }

        $srv = new GetBloggersService($pageSize, $userRepository, $microPostRepository);

        $dto = $srv->getBloggers($page);
        self::assertSame($slicedUserCollection, $dto->getBloggers());
        self::assertEquals($page, $dto->getPaginatorDto()->getPage());
        self::assertEquals($pageSize, $dto->getPaginatorDto()->getPageSize());
        self::assertEquals(ceil($total / $pageSize), $dto->getPaginatorDto()->getTotalPages());
    }

    protected function getUserCollection(int $total): Collection
    {
        $collection = new ArrayCollection();

        for ($i = 0; $i < $total; $i++) {
            $collection->add(new User());
        }

        return $collection;
    }
}
