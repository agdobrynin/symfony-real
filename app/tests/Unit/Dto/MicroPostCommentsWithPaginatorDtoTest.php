<?php
declare(strict_types=1);

namespace App\Tests\Unit\Dto;

use App\Dto\Exception\MicroPostCommentsWithPaginatorDtoException;
use App\Dto\MicroPostCommentsWithPaginatorDto;
use App\Dto\PaginatorDto;
use App\Entity\Comment;
use PHPUnit\Framework\TestCase;

class MicroPostCommentsWithPaginatorDtoTest extends TestCase
{
    public function sourceData(): \Generator
    {
        yield 'success' => [[new Comment()], new PaginatorDto(1, 1, 1), null];
        yield 'fail' => [[new class {
        }], new PaginatorDto(1, 1, 1), MicroPostCommentsWithPaginatorDtoException::class];
    }

    /**
     * @dataProvider sourceData
     */
    public function testMicroPostCommentsWithPaginatorDto(array $comments, PaginatorDto $paginatorDto, ?string $expectExceptionClass): void
    {
        if ($expectExceptionClass) {
            self::expectException($expectExceptionClass);
        }

        $dto = new MicroPostCommentsWithPaginatorDto($comments, $paginatorDto);
        self::assertTrue($dto->getPaginatorDto() instanceof PaginatorDto);

        if (count($dto->getComments())) {
            self::assertTrue(($dto->getComments()[0] ?? []) instanceof Comment);
        }
    }
}
