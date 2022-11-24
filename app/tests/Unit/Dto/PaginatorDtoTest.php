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
        yield 'success for page 1' => [new PaginatorProviderData(1, 5, 2, 0, 3, null)];
        yield 'success for page 2' => [new PaginatorProviderData(2, 5, 2, 2, 3, null)];
        yield 'fail page is too large' => [new PaginatorProviderData(20, 5, 2, 2, 3, PaginatorDtoPageException::class)];
        yield 'fail page size is wrong' => [new PaginatorProviderData(1, 5, 0, 2, 3, PaginatorDtoPageSizeException::class)];
        yield 'fail page less then 1' => [new PaginatorProviderData(0, 5, 0, 2, 3, PaginatorDtoException::class)];
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
