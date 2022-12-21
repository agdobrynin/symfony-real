<?php
declare(strict_types=1);

namespace App\Tests\Unit\Service\MicroPost;

use App\Dto\Exception\PaginatorDtoPageException;
use App\Entity\MicroPost;
use App\Repository\MicroPostRepository;
use App\Service\MicroPost\GetMicroPostSoftDeleteService;
use App\Service\MicroPost\SoftDeleteFilterServiceInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
        $paginator = self::createMock(Paginator::class);
        $paginator->method('count')->willReturn($total);
        $paginator->method('getIterator')->willReturn($this->getCollection($total));

        $microPostRepository = self::createMock(MicroPostRepository::class);
        $microPostRepository->expects(self::once())
            ->method('getAllWithPaginatorOrderByDeleteAt')
            ->with($page, $pageSize)
            ->willReturn($paginator);

        $softDeleteFilterService = self::createMock(SoftDeleteFilterServiceInterface::class);
        $softDeleteFilterService->expects(self::once())->method('softDeleteOnlyOn');
        $softDeleteFilterService->expects($expectException ? self::never() : self::once())->method('allOff');

        if ($expectException) {
            self::expectException($expectException);
        }

        $srv = new GetMicroPostSoftDeleteService($microPostRepository, $softDeleteFilterService);
        $srv->get($page, $pageSize);
    }


    private function getCollection(int $total): \ArrayIterator
    {
        $collection = new \ArrayIterator();

        for ($i = 0; $i < $total; $i++) {
            $mp = (new MicroPost())->setContent('content ' . $i)->setDeleteAt(new \DateTime());
            $collection->append($mp);
        }

        return $collection;
    }

}
