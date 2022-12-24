<?php
declare(strict_types=1);

namespace App\Dto;

use App\Dto\Exception\BloggersWithPaginatorDtoException;
use App\Entity\User;

class BloggersWithPaginatorDto
{
    private $bloggers;
    private $paginatorDto;

    public function __construct(array $bloggers, PaginatorDto $paginatorDto)
    {
        if (count($bloggers) && (!$bloggers[0] instanceof User)) {
            $message = sprintf('Params bloggers includes only "%s" objects. Got "%s"', User::class, \get_class($bloggers[0]));

            throw new BloggersWithPaginatorDtoException($message);
        }

        $this->bloggers = $bloggers;
        $this->paginatorDto = $paginatorDto;
    }

    public function getBloggers(): array
    {
        return $this->bloggers;
    }

    public function getPaginatorDto(): PaginatorDto
    {
        return $this->paginatorDto;
    }
}
