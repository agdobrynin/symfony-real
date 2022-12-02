<?php
declare(strict_types=1);

namespace App\Tests\Unit\Service\MicroPost;

use App\Repository\Filter\SoftDeleteFilter;
use App\Repository\Filter\SoftDeleteOnlyFilter;
use App\Service\MicroPost\SoftDeleteFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\FilterCollection;
use PHPUnit\Framework\TestCase;

class SoftDeleteFilterServiceTest extends TestCase
{
    public function testServiceSoftDeleteOnlyOn(): void
    {
        $filters = self::createMock(FilterCollection::class);
        $filters->expects(self::once())
            ->method('enable')->with(SoftDeleteOnlyFilter::NAME);

        $em = self::createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('getFilters')->willReturn($filters);

        $srv = new SoftDeleteFilterService($em);
        $srv->softDeleteOnlyOn();
    }

    public function testServiceSoftDeleteOn(): void
    {
        $filters = self::createMock(FilterCollection::class);
        $filters->expects(self::once())
            ->method('enable')->with(SoftDeleteFilter::NAME);

        $em = self::createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('getFilters')->willReturn($filters);

        $srv = new SoftDeleteFilterService($em);
        $srv->softDeletedOn();
    }

    public function testServiceAllOff(): void
    {
        $filters = self::createMock(FilterCollection::class);

        $filters->method('isEnabled')->willReturn(true);

        $filters->expects(self::atLeastOnce())
            ->method('disable')
            ->with(self::logicalOr(
                SoftDeleteOnlyFilter::NAME, SoftDeleteFilter::NAME
            ));

        $em = self::createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('getFilters')->willReturn($filters);

        $srv = new SoftDeleteFilterService($em);
        $srv->allOff();
    }
}
