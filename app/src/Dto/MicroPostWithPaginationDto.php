<?php
declare(strict_types=1);

namespace App\Dto;

use App\Dto\Exception\MicroPostWithPaginationDtoException;
use App\Entity\MicroPost;

class MicroPostWithPaginationDto
{
    protected $posts;
    private $paginatorDto;

    public function __construct(\ArrayIterator $posts, PaginatorDto $paginatorDto)
    {
        if ($posts->count() && (!$posts[0] instanceof MicroPost)) {
            $message = sprintf('Params posts includes only "%s" objects. Got "%s"', MicroPost::class, \get_class($posts[0]));

            throw new MicroPostWithPaginationDtoException($message);
        }

        $this->posts = $posts;
        $this->paginatorDto = $paginatorDto;
    }

    public function getPaginatorDto(): PaginatorDto
    {
        return $this->paginatorDto;
    }

    public function getPosts(): \ArrayIterator
    {
        return $this->posts;
    }
}
