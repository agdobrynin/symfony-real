<?php
declare(strict_types=1);

namespace App\Service\MicroPost;

use App\Dto\BloggersWithPaginatorDto;
use App\Dto\PaginatorDto;
use App\Repository\UserRepository;

class GetBloggersService implements GetBloggersServiceInterface
{
    private $pageSize;
    private $userRepository;

    public function __construct(int $pageSize, UserRepository $userRepository)
    {
        $this->pageSize = $pageSize;
        $this->userRepository = $userRepository;
    }

    public function getBloggers(int $page): BloggersWithPaginatorDto
    {
        $totalItems = $this->userRepository->getCountBloggersWithPosts();
        $paginatorDto = new PaginatorDto($page, $totalItems, $this->pageSize);
        $bloggers = $this->userRepository->getBloggersWithPostsByPaginator($paginatorDto);

        return new BloggersWithPaginatorDto($bloggers, $paginatorDto);
    }
}
