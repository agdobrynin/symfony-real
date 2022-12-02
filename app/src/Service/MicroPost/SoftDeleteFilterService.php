<?php
declare(strict_types=1);

namespace App\Service\MicroPost;

use App\Repository\Filter\SoftDeleteFilter;
use App\Repository\Filter\SoftDeleteOnlyFilter;
use Doctrine\ORM\EntityManagerInterface;

class SoftDeleteFilterService implements SoftDeleteFilterServiceInterface
{
    private const FILTERS_NAMES = [SoftDeleteFilter::NAME, SoftDeleteOnlyFilter::NAME];

    private $filters;

    public function __construct(EntityManagerInterface $em)
    {
        $this->filters = $em->getFilters();
    }

    public function softDeleteOnlyOn(): void
    {
        $this->checkAndDisable(SoftDeleteFilter::NAME);
        $this->filters->enable(SoftDeleteOnlyFilter::NAME);
    }

    public function softDeletedOn(): void
    {
        $this->checkAndDisable(SoftDeleteOnlyFilter::NAME);
        $this->filters->enable(SoftDeleteFilter::NAME);
    }

    public function allOff(): void
    {
        foreach (self::FILTERS_NAMES as $filterName) {
            if ($this->filters->isEnabled($filterName)) {
                $this->filters->disable($filterName);
            }
        }
    }

    protected function checkAndDisable(string $filterName): void
    {
        if ($this->filters->isEnabled($filterName)) {
            $this->filters->disable($filterName);
        }
    }
}
