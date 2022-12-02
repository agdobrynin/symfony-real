<?php
declare(strict_types=1);

namespace App\Tests\Unit\Service\MicroPost;

use App\Dto\Exception\PaginatorDtoPageException;
use App\Entity\MicroPost;
use App\Repository\MicroPostRepository;
use App\Service\MicroPost\GetMicroPostSoftDeleteService;
use App\Service\MicroPost\SoftDeleteFilterServiceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class GetMicroPostSoftDeleteServiceTest extends TestCase
{
    public function dataSource(): \Generator
    {
        yield 'Success get microposts for page 1' => [15, 10, 1, null];
        yield 'Success get microposts for page 2' => [15, 10, 2, null];
        yield 'Wrong get microposts for page  more then maximum pages' => [1, 1, 2, PaginatorDtoPageException::class];
        yield 'Success get from empty array' => [0, 1, 1, null];
        yield 'Wrong get micropost for page  less then 1' => [0, 10, 0, PaginatorDtoPageException::class];
    }

    /**
     * @dataProvider dataSource
     */
    public function testGet(int $total, int $pageSize, int $page, ?string $expectException): void
    {
        $microPostRepository = self::createMock(MicroPostRepository::class);
        $microPostRepository->expects(self::once())
            ->method('getAllCount')->willReturn($total);

        $softDeleteFilterService = self::createMock(SoftDeleteFilterServiceInterface::class);
        $softDeleteFilterService->expects(self::once())->method('softDeleteOnlyOn');

        $collection = $this->getCollection($total);
        $slicedCollection = [];

        $expects = self::atLeastOnce();

        if ($expectException) {
            self::expectException($expectException);
            $expects = self::never();
        } else {
            $firstIndex = ($page - 1) * $pageSize;
            $slicedCollection = array_values($collection->slice($firstIndex, $pageSize));
        }

        $microPostRepository->expects($expects)
            ->method('getAllWithPaginatorOrderByDeleteAt')
            ->willReturn($slicedCollection);

        $softDeleteFilterService->expects($expects)->method('allOff');

        $srv = new GetMicroPostSoftDeleteService($microPostRepository, $softDeleteFilterService);
        $dto = $srv->get($page, $pageSize);

        self::assertSame($slicedCollection, $dto->getPosts());
        self::assertEquals($page, $dto->getPaginatorDto()->getPage());
        self::assertEquals($pageSize, $dto->getPaginatorDto()->getPageSize());
        self::assertEquals(ceil($total / $pageSize), $dto->getPaginatorDto()->getTotalPages());
    }

    private function getCollection(int $total): Collection
    {
        $collection = new ArrayCollection();

        for ($i = 0; $i < $total; $i++) {
            $mp = (new MicroPost())->setContent('content ' . $i)->setDeleteAt(new \DateTime());
            $collection->add($mp);
        }

        return $collection;
    }
}
