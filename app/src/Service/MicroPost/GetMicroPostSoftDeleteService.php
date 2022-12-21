<?php
declare(strict_types=1);

namespace App\Service\MicroPost;

use App\Dto\MicroPostWithPaginationDto;
use App\Dto\PaginatorDto;
use App\Repository\MicroPostRepository;

class GetMicroPostSoftDeleteService implements GetMicroPostSoftDeleteServiceInterface
{
    private $microPostRepository;
    private $softDeleteFilterService;

    public function __construct(
        MicroPostRepository              $microPostRepository,
        SoftDeleteFilterServiceInterface $softDeleteFilterService
    )
    {
        $this->microPostRepository = $microPostRepository;
        $this->softDeleteFilterService = $softDeleteFilterService;
    }

    public function get(int $page, int $pageSize): MicroPostWithPaginationDto
    {
        $this->softDeleteFilterService->softDeleteOnlyOn();

        $posts = $this->microPostRepository->getAllWithPaginatorOrderByDeleteAt($page, $pageSize);
        $paginatorDto = new PaginatorDto($page, $posts->count(), $pageSize);

        $this->softDeleteFilterService->allOff();

        return new MicroPostWithPaginationDto($posts->getIterator(), $paginatorDto);
    }
}
