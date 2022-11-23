<?php

namespace App\Service\MicroPost;

use App\Dto\BloggersWithPaginatorDto;

interface GetBloggersServiceInterface
{
    public function getBloggers(int $page): BloggersWithPaginatorDto;
}
