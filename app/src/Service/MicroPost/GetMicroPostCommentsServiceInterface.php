<?php

namespace App\Service\MicroPost;

use App\Dto\MicroPostCommentsWithPaginatorDto;
use App\Entity\MicroPost;

interface GetMicroPostCommentsServiceInterface
{
    public function getComments(int $page, MicroPost $microPost): MicroPostCommentsWithPaginatorDto;
}
