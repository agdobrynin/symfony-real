<?php
declare(strict_types=1);

namespace App\Dto;

use App\Dto\Exception\PaginatorDtoException;

class PaginatorDto
{
    protected $page;
    protected $totalPages;
    protected $pageSize;

    public function __construct(int $page, int $totalItems, int $pageSize)
    {
        if ($page < 1) {
            throw new PaginatorDtoException(sprintf('Parameter "page" must be positive value. Got "%s"', $page));
        }

        if ($pageSize < 1) {
            throw new PaginatorDtoException(sprintf('Parameter "pageSize" must be positive value. Got "%s"', $pageSize));
        }

        $this->pageSize = $pageSize;

        $this->totalPages = (int)ceil($totalItems / $pageSize);

        if ($this->totalPages && $page > $this->totalPages) {
            throw new PaginatorDtoException(
                sprintf('Parameter "page" must be less or equal "%s".', $this->totalPages));
        }

        $this->page = $page;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function getFirstResultIndex(): int
    {
        return ($this->page - 1) * $this->pageSize;
    }
}
