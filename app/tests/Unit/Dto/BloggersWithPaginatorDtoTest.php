<?php
declare(strict_types=1);

namespace App\Tests\Unit\Dto;

use App\Dto\BloggersWithPaginatorDto;
use App\Dto\Exception\BloggersWithPaginatorDtoException;
use App\Dto\PaginatorDto;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class BloggersWithPaginatorDtoTest extends TestCase
{
    public function sourceData(): \Generator
    {
        yield 'success' => [$this->getUserCollection(11), new PaginatorDto(1, 11, 10), null];
        yield 'fail' => [new \ArrayIterator([new class {
        }]), new PaginatorDto(1, 1, 1), BloggersWithPaginatorDtoException::class];
    }

    /**
     * @dataProvider sourceData
     */
    public function testBloggersWithPagination(\ArrayIterator $bloggers, PaginatorDto $paginatorDto, ?string $expectException): void
    {
        if ($expectException) {
            self::expectException($expectException);
        }

        $dto = new BloggersWithPaginatorDto($bloggers, $paginatorDto);
        self::assertSame($bloggers, $dto->getBloggers());
        self::assertEquals($paginatorDto->getPage(), $dto->getPaginatorDto()->getPage());
        self::assertEquals($paginatorDto->getPageSize(), $dto->getPaginatorDto()->getPageSize());
        self::assertEquals($paginatorDto->getTotalPages(), $dto->getPaginatorDto()->getTotalPages());
    }

    protected function getUserCollection(int $count): \ArrayIterator
    {
        $a = [];

        for ($i = 0; $i < $count; $i++) {
            $a[] = new User();
        }

        return new \ArrayIterator($a);
    }
}
