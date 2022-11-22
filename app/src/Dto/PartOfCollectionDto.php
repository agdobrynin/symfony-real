<?php
declare(strict_types=1);

namespace App\Dto;

use Doctrine\Common\Collections\Collection;

class PartOfCollectionDto
{
    public $total;
    public $collection;
    public $remainder;

    public function __construct(Collection $collection, int $length)
    {
        $this->total = $collection->count();
        $this->collection = $collection->slice(0, $length);
        $this->remainder = $this->total > $length ? $this->total - $length : 0;
    }
}
