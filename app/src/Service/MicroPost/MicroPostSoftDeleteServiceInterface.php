<?php

namespace App\Service\MicroPost;

use App\Dto\MicroPostWithPaginationDto;
use App\Entity\MicroPost;

interface MicroPostSoftDeleteServiceInterface
{
    public function get(int $page): MicroPostWithPaginationDto;

    public function restore(MicroPost $microPost): void;
}
