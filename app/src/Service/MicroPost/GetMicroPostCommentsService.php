<?php
declare(strict_types=1);

namespace App\Service\MicroPost;

use App\Dto\MicroPostCommentsWithPaginatorDto;
use App\Dto\PaginatorDto;
use App\Entity\MicroPost;
use App\Repository\CommentRepository;

class GetMicroPostCommentsService implements GetMicroPostCommentsServiceInterface
{
    private $pageSize;
    private $commentRepository;

    public function __construct(int $pageSize, CommentRepository $commentRepository)
    {
        $this->pageSize = $pageSize;
        $this->commentRepository = $commentRepository;
    }

    public function getComments(int $page, MicroPost $microPost): MicroPostCommentsWithPaginatorDto
    {
        $comments = $this->commentRepository->getCommentsByMicroPost($page, $this->pageSize, $microPost);
        $paginatorDto = new PaginatorDto($page, $comments->count(), $this->pageSize);

        return new MicroPostCommentsWithPaginatorDto($comments->getIterator(), $paginatorDto);
    }
}
