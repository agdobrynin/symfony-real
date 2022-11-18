<?php
declare(strict_types=1);

namespace App\Service\MicroPost;

use App\Dto\MicroPostCommentsWithPaginatorDto;
use App\Dto\PaginatorDto;
use App\Entity\MicroPost;

class GetMicroPostCommentsService implements GetMicroPostCommentsServiceInterface
{
    private $pageSize;

    public function __construct(int $pageSize)
    {
        $this->pageSize = $pageSize;
    }

    public function getComments(int $page, MicroPost $microPost): MicroPostCommentsWithPaginatorDto
    {
        $paginatorDto = new PaginatorDto($page, $microPost->getComments()->count(), $this->pageSize);

        $comments = $microPost->getComments()
            ->slice($paginatorDto->getFirstResultIndex(), $paginatorDto->getPageSize());

        return new MicroPostCommentsWithPaginatorDto($comments, $paginatorDto);
    }
}
