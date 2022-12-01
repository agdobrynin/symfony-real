<?php

namespace App\Service\MicroPost;

use App\Dto\MicroPostWithPaginationDto;
use App\Entity\User;

interface GetMicroPostsServiceInterface
{
    public function findFollowingMicroPosts(User $user, int $page): MicroPostWithPaginationDto;

    public function findMicroPostsByUser(User $user, int $page): MicroPostWithPaginationDto;

    public function findLastMicroPostsOrderByDate(int $page): MicroPostWithPaginationDto;

    public function findLastSoftDeletedMicroPostsOrderByDeleteAt(int $page): MicroPostWithPaginationDto;
}
