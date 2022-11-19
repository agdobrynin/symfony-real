<?php
declare(strict_types=1);

namespace App\Tests\Unit\Dto;

class PaginatorProviderData
{
    public $page;
    public $total;
    public $pageSize;
    public $expectFirstIndex;
    public $expectPages;
    public $expectExceptionClass;

    public function __construct(int $page, int $total, int $pageSize, int $expectFirstIndex, int $expectPages, ?string $expectExceptionClass)
    {
        $this->page = $page;
        $this->total = $total;
        $this->pageSize = $pageSize;
        $this->expectFirstIndex = $expectFirstIndex;
        $this->expectPages = $expectPages;
        $this->expectExceptionClass = $expectExceptionClass;
    }
}
