<?php

namespace App\Service;

use App\Dto\SimplePostDto;

interface PostStoreInterface
{
    public function add(SimplePostDto $postDto): SimplePostDto;

    public function get(string $uuid): ?SimplePostDto;

    /**
     * @return SimplePostDto[]
     */
    public function list(int $page): array;

    public function getPageCount(): int;

    public function setPageSize(int $pageSize): void;

    public function destroy(): void;
}
