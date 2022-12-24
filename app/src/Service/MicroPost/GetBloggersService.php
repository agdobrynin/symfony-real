<?php
declare(strict_types=1);

namespace App\Service\MicroPost;

use App\Dto\BloggersWithPaginatorDto;
use App\Dto\PaginatorDto;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;

class GetBloggersService implements GetBloggersServiceInterface
{
    private $pageSize;
    private $userRepository;
    private $commentRepository;

    public function __construct(int $pageSize, UserRepository $userRepository, CommentRepository $commentRepository)
    {
        $this->pageSize = $pageSize;
        $this->userRepository = $userRepository;
        $this->commentRepository = $commentRepository;
    }

    public function getBloggers(int $page): BloggersWithPaginatorDto
    {
        $totalItems = $this->userRepository->getCountBloggersWithPosts();
        $bloggers = (array)$this->userRepository
            ->getBloggersWithPostsByPaginator($page, $this->pageSize)
            ->getIterator();
        $paginatorDto = new PaginatorDto($page, $totalItems, $this->pageSize);
        $this->commentRepository->fillTotalCommentsToUsers($bloggers);

        return new BloggersWithPaginatorDto($bloggers, $paginatorDto);
    }
}
