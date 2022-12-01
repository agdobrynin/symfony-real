<?php
declare(strict_types=1);

namespace App\Service\MicroPost;

use App\Dto\MicroPostWithPaginationDto;
use App\Dto\PaginatorDto;
use App\Entity\MicroPost;
use App\Repository\Filter\SoftDeleteFilter;
use App\Repository\Filter\SoftDeleteOnlyFilter;
use App\Repository\MicroPostRepository;
use Doctrine\ORM\EntityManagerInterface;

class MicroPostSoftDeleteService implements MicroPostSoftDeleteServiceInterface
{
    private $em;
    private $microPostRepository;
    private $pageSize;

    public function __construct(EntityManagerInterface $em, MicroPostRepository $microPostRepository, int $pageSize)
    {
        $this->em = $em;
        $this->microPostRepository = $microPostRepository;
        $this->pageSize = $pageSize;

        $filters = $this->em->getFilters();
        $filters->enable(SoftDeleteOnlyFilter::NAME);
        $filters->disable(SoftDeleteFilter::NAME);
    }

    public function __destruct()
    {
        $filters = $this->em->getFilters();
        $filters->disable(SoftDeleteOnlyFilter::NAME);
        $filters->enable(SoftDeleteFilter::NAME);
    }

    public function get(int $page): MicroPostWithPaginationDto
    {
        $totalItems = $this->microPostRepository->getAllCount();
        $paginatorDto = new PaginatorDto($page, $totalItems, $this->pageSize);
        $posts = $this->microPostRepository->getAllWithPaginatorOrderByDeleteAt($paginatorDto);

        return new MicroPostWithPaginationDto($posts, $paginatorDto);
    }

    public function restore(MicroPost $microPost): void
    {
        $microPost->setDeleteAt(null);
        $this->em->persist($microPost);
        $this->em->flush();
    }
}
