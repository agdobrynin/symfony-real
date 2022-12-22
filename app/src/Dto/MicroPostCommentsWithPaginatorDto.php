<?php
declare(strict_types=1);

namespace App\Dto;

use App\Dto\Exception\MicroPostCommentsWithPaginatorDtoException;
use App\Entity\Comment;

class MicroPostCommentsWithPaginatorDto
{
    protected $comments;
    private $paginatorDto;

    public function __construct(\ArrayIterator $comments, PaginatorDto $paginatorDto)
    {
        if (count($comments) && (!$comments[0] instanceof Comment)) {
            $message = sprintf('Params comments includes only "%s" objects. Got "%s"', Comment::class, \get_class($comments[0]));

            throw new MicroPostCommentsWithPaginatorDtoException($message);
        }

        $this->comments = $comments;
        $this->paginatorDto = $paginatorDto;
    }

    public function getComments(): \ArrayIterator
    {
        return $this->comments;
    }

    public function getPaginatorDto(): PaginatorDto
    {
        return $this->paginatorDto;
    }
}
