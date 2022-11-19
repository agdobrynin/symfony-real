<?php
declare(strict_types=1);

namespace App\Tests\Unit\Dto;

use App\Dto\Exception\PaginatorDtoException;
use App\Dto\Exception\PaginatorDtoPageException;
use App\Dto\Exception\PaginatorDtoPageSizeException;
use App\Dto\PaginatorDto;
use PHPUnit\Framework\TestCase;

class PaginatorDtoTest extends TestCase
{
    public function paginator(): \Generator
    {
        yield [new PaginatorProviderData(1, 5, 2, 0, 3, null)];
        yield [new PaginatorProviderData(2, 5, 2, 2, 3, null)];
        // current page is too large
        yield [new PaginatorProviderData(20, 5, 2, 2, 3, PaginatorDtoPageException::class)];
        // page  size less than 1
        yield [new PaginatorProviderData(1, 5, 0, 2, 3, PaginatorDtoPageSizeException::class)];
        // current page less than 1
        yield [new PaginatorProviderData(0, 5, 0, 2, 3, PaginatorDtoException::class)];
    }

    /**
     * @dataProvider paginator
     */
    public function testPaginatorDto(PaginatorProviderData $data): void
    {
        if ($data->expectExceptionClass) {
            self::expectException($data->expectExceptionClass);
        }

        $dto = new PaginatorDto($data->page, $data->total, $data->pageSize);

        self::assertEquals($data->page, $dto->getPage());
        self::assertEquals($data->pageSize, $dto->getPageSize());
        self::assertEquals($data->expectPages, $dto->getTotalPages());
        self::assertEquals($data->expectFirstIndex, $dto->getFirstResultIndex());
    }
}
