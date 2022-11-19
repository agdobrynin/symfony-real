<?php
declare(strict_types=1);

namespace App\Tests\Unit\Dto;

use App\Dto\Exception\MicroPostWithPaginationDtoException;
use App\Dto\MicroPostWithPaginationDto;
use App\Dto\PaginatorDto;
use App\Entity\MicroPost;
use PHPUnit\Framework\TestCase;

class MicroPostWithPaginationDtoTest extends TestCase
{
    public function sourceData(): \Generator
    {
        yield [[new MicroPost()], new PaginatorDto(1, 1, 1), null];
        yield [[new class {
        }], new PaginatorDto(1, 1, 1), MicroPostWithPaginationDtoException::class];
    }

    /**
     * @dataProvider sourceData
     */
    public function testMicroPostWithPaginationDto(array $microPosts, PaginatorDto $paginatorDto, ?string $expectExceptionClass): void
    {
        if ($expectExceptionClass) {
            self::expectException($expectExceptionClass);
        }

        $dto = new MicroPostWithPaginationDto($microPosts, $paginatorDto);
        self::assertTrue($dto->getPaginatorDto() instanceof PaginatorDto);

        if (count($dto->getPosts())) {
            self::assertTrue(($dto->getPosts()[0] ?? []) instanceof MicroPost);
        }
    }
}
