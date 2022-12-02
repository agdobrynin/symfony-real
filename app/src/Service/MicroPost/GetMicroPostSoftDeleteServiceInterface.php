<?php

namespace App\Service\MicroPost;

use App\Dto\MicroPostWithPaginationDto;

interface GetMicroPostSoftDeleteServiceInterface
{
    public function get(int $page, int $pageSize): MicroPostWithPaginationDto;
}
