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
    public function list(): array;

    public function destroy(): void;
}
