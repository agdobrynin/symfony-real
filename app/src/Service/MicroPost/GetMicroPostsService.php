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

        $paginationDto = $this->getPaginatorDto($page, function () use ($followingUsers) {
            return $this->microPostRepository->getCountByUsers($followingUsers);
        });

        $posts = $this->microPostRepository->findAllByUsersWithPaginator($followingUsers, $paginationDto);

        return new MicroPostWithPaginationDto($posts, $paginationDto);
    }

    public function findMicroPostsByUser(User $user, int $page): MicroPostWithPaginationDto
    {
        $paginatorDto = $this->getPaginatorDto($page, function () use ($user) {
            return $this->microPostRepository->getCountByUser($user);
        });

        $posts = $this->microPostRepository->findByUserWithPaginator($user, $paginatorDto);

        return new MicroPostWithPaginationDto($posts, $paginatorDto);
    }

    public function findLastMicroPosts(int $page): MicroPostWithPaginationDto
    {
        $paginatorDto = $this->getPaginatorDto($page, function () {
            return $this->microPostRepository->getAllCount();
        });

        $posts = $this->microPostRepository->getAllWithPaginator($paginatorDto);

        return new MicroPostWithPaginationDto($posts, $paginatorDto);
    }

    protected function getPaginatorDto(int $page, \Closure $closure): PaginatorDto
    {
        $totalItems = $closure();

        return new PaginatorDto($page, $totalItems, $this->pageSize);
    }
}
