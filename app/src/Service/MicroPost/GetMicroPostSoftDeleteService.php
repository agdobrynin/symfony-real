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

        $totalItems = $this->microPostRepository->getAllCount();
        $paginatorDto = new PaginatorDto($page, $totalItems, $pageSize);
        $posts = $this->microPostRepository->getAllWithPaginatorOrderByDeleteAt($paginatorDto);

        $this->softDeleteFilterService->allOff();

        return new MicroPostWithPaginationDto($posts, $paginatorDto);
    }
}
