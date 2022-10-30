<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\SimplePostDto;
use Symfony\Component\HttpFoundation\RequestStack;

class PostStore implements PostStoreInterface
{
    private const KEY_POSTS = 'posts';

    private $requestStack;
    private $pageSize;

    public function __construct(RequestStack $requestStack, int $pageSize)
    {
        $this->requestStack = $requestStack;
        $this->pageSize = $pageSize;
    }

    public function add(SimplePostDto $postDto): SimplePostDto
    {
        if (null === $postDto->uuid) {
            $postDto->uuid = uniqid();
        }

        $posts = $this->requestStack->getSession()->get(self::KEY_POSTS, []);
        $posts[$postDto->uuid] = $postDto;
        $this->requestStack->getSession()->set(self::KEY_POSTS, $posts);

        return $postDto;
    }

    public function get(string $uuid): ?SimplePostDto
    {
        return $this->requestStack->getSession()->get(self::KEY_POSTS, [])[$uuid] ?? null;
    }

    /**
     * @return SimplePostDto[]
     */
    public function list(int $page): array
    {
        $offset = ($page - 1) * $this->pageSize;

        return array_slice($this->requestStack->getSession()->get(self::KEY_POSTS, []), $offset, $this->pageSize);
    }

    public function getPageCount(): int
    {
        $postCount = count($this->requestStack->getSession()->get(self::KEY_POSTS, []));

        return (int)ceil($postCount / $this->pageSize);
    }

    public function setPageSize(int $pageSize): void
    {
        $this->pageSize = $pageSize;
    }

    public function destroy(): void
    {
        $this->requestStack->getSession()->remove(self::KEY_POSTS);
    }
}
