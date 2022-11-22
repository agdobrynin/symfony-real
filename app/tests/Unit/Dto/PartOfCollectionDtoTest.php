<?php
declare(strict_types=1);

namespace App\Tests\Unit\Dto;

use App\Dto\PartOfCollectionDto;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class PartOfCollectionDtoTest extends TestCase
{
    public function testEmptyCollection(): void
    {
        $dto = new PartOfCollectionDto($this->getCollection(0), 10);

        self::assertEquals(0, $dto->total);
        self::assertEquals(0, $dto->remainder);
        self::assertSameSize([], $dto->collection);
    }

    public function testCollectionNoRemainder(): void
    {
        $dto = new PartOfCollectionDto($this->getCollection(10), 10);

        self::assertEquals(10, $dto->total);
        self::assertEquals(0, $dto->remainder);
        self::assertCount(10, $dto->collection);
    }

    public function testCollectionWithRemainder(): void
    {
        $dto = new PartOfCollectionDto($this->getCollection(10), 3);

        self::assertEquals(10, $dto->total);
        self::assertEquals(7, $dto->remainder);
        self::assertCount(3, $dto->collection);
    }

    protected function getCollection(int $count): ArrayCollection
    {
        $collection = new ArrayCollection();

        for ($i = 0; $i < $count; $i++) {
            $collection->add(random_bytes(4));
        }

        return $collection;
    }
}
