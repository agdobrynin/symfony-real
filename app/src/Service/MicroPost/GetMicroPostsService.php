<?php
declare(strict_types=1);

namespace App\Service\MicroPost;

use App\Dto\MicroPostWithPaginationDto;
use App\Dto\PaginatorDto;
use App\Entity\User;
use App\Repository\MicroPostRepository;

class GetMicroPostsService implements GetMicroPostsServiceInterface
{
    private $microPostRepository;
    private $pageSize;

    public function __construct(MicroPostRepository $microPostRepository, int $pageSize)
    {
        $this->microPostRepository = $microPostRepository;
        $this->pageSize = $pageSize;
    }

    public function findFollowingMicroPosts(User $user, int $page): MicroPostWithPaginationDto
    {
        $posts = $this->microPostRepository->findFollowingMicroPostWithPaginator($user, $page, $this->pageSize);
        $paginationDto = new PaginatorDto($page, $posts->count(), $this->pageSize);

        return new MicroPostWithPaginationDto($posts->getIterator(), $paginationDto);
    }

    public function findMicroPostsByUser(User $user, int $page): MicroPostWithPaginationDto
    {
        $posts = $this->microPostRepository->findMicroPostByUserWithPaginator($user, $page, $this->pageSize);
        $paginatorDto = new PaginatorDto($page, $posts->count(), $this->pageSize);

        return new MicroPostWithPaginationDto($posts->getIterator(), $paginatorDto);
    }

    public function findLastMicroPostsOrderByDate(int $page): MicroPostWithPaginationDto
    {
        $posts = $this->microPostRepository->getAllWithPaginator($page, $this->pageSize);
        $paginatorDto = new PaginatorDto($page, $posts->count(), $this->pageSize);

        return new MicroPostWithPaginationDto($posts->getIterator(), $paginatorDto);
    }
}
