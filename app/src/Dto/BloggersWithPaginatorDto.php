<?php
declare(strict_types=1);

namespace App\Dto;

use App\Dto\Exception\BloggersWithPaginatorDtoException;
use App\Entity\User;

class BloggersWithPaginatorDto
{
    private $bloggers;
    private $paginatorDto;
    private $commentsCountByUserUuid;

    public function __construct(array $bloggers, PaginatorDto $paginatorDto, array $commentsCountByUserUuid)
    {
        if (count($bloggers) && (!$bloggers[0] instanceof User)) {
            $message = sprintf('Params bloggers includes only "%s" objects. Got "%s"', User::class, \get_class($bloggers[0]));

            throw new BloggersWithPaginatorDtoException($message);
        }

        $this->bloggers = $bloggers;
        $this->paginatorDto = $paginatorDto;
        $this->commentsCountByUserUuid = $commentsCountByUserUuid;
    }

    public function getBloggers(): array
    {
        return $this->bloggers;
    }

    public function getPaginatorDto(): PaginatorDto
    {
        return $this->paginatorDto;
    }

    public function getCommentsCountByUser(User $user): ?int
    {
        return $this->commentsCountByUserUuid[$user->getUuid()->toRfc4122()] ?? null;
    }
}
