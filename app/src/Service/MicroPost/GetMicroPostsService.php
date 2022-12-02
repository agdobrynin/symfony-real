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
        $followingUsers = $user->getFollowing();
        $totalItems = $this->microPostRepository->getCountByUsers($followingUsers);
        $paginationDto = new PaginatorDto($page, $totalItems, $this->pageSize);
        $posts = $this->microPostRepository->findAllByUsersWithPaginator($followingUsers, $paginationDto);

        return new MicroPostWithPaginationDto($posts, $paginationDto);
    }

    public function findMicroPostsByUser(User $user, int $page): MicroPostWithPaginationDto
    {
        $totalItems = $this->microPostRepository->getCountByUser($user);
        $paginatorDto = new PaginatorDto($page, $totalItems, $this->pageSize);
        $posts = $this->microPostRepository->findByUserWithPaginator($user, $paginatorDto);

        return new MicroPostWithPaginationDto($posts, $paginatorDto);
    }

    public function findLastMicroPostsOrderByDate(int $page): MicroPostWithPaginationDto
    {
        $totalItems = $this->microPostRepository->getAllCount();
        $paginatorDto = new PaginatorDto($page, $totalItems, $this->pageSize);
        $posts = $this->microPostRepository->getAllWithPaginatorOrderByDate($paginatorDto);

        return new MicroPostWithPaginationDto($posts, $paginatorDto);
    }
}
