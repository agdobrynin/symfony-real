<?php
declare(strict_types=1);

namespace App\Dto;

use App\Dto\Exception\MicroPostWithPaginationDtoException;
use App\Entity\MicroPost;

class MicroPostWithPaginationDto
{
    /**
     * @var MicroPost[]
     */
    protected $posts;
    private $paginatorDto;

    public function __construct(array $posts, PaginatorDto $paginatorDto)
    {
        if (count($posts) && (!$posts[0] instanceof MicroPost)) {
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

    /**
     * @return MicroPost[]
     */
    public function getPosts(): array
    {
        return $this->posts;
    }
}
