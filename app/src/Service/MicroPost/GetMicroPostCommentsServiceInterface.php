<?php

namespace App\Service\MicroPost;


use App\Dto\MicroPostCommentsWithPaginatorDto;
use App\Entity\MicroPost;

interface GetMicroPostCommentsServiceInterface
{
    /**
     * @throws \App\Dto\Exception\PaginatorDtoPageSizeException|\App\Dto\Exception\PaginatorDtoPageException
     */
    public function getComments(int $page, MicroPost $microPost): MicroPostCommentsWithPaginatorDto;
}
