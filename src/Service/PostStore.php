<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\SimplePostDto;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PostStore implements PostStoreInterface
{
    private const KEY_POSTS = 'posts';

    private $session;
    private $pageSize;

    public function __construct(SessionInterface $session, int $pageSize)
    {
        $this->session = $session;
        $this->pageSize = $pageSize;
    }

    public function add(SimplePostDto $postDto): SimplePostDto
    {
        if (null === $postDto->uuid) {
            $postDto->uuid = uniqid();
        }

        $posts = $this->session->get(self::KEY_POSTS, []);
        $posts[$postDto->uuid] = $postDto;
        $this->session->set(self::KEY_POSTS, $posts);

        return $postDto;
    }

    public function get(string $uuid): ?SimplePostDto
    {
        return $this->session->get(self::KEY_POSTS, [])[$uuid] ?? null;
    }

    /**
     * @return SimplePostDto[]
     */
    public function list(int $page): array
    {
        $offset = ($page - 1) * $this->pageSize;

        return array_slice($this->session->get(self::KEY_POSTS, []), $offset, $this->pageSize);
    }

    public function getPageCount(): int
    {
        $postCount = count($this->session->get(self::KEY_POSTS, []));

        return (int)ceil($postCount / $this->pageSize);
    }

    public function setPageSize(int $pageSize): void
    {
        $this->pageSize = $pageSize;
    }

    public function destroy(): void
    {
        $this->session->remove(self::KEY_POSTS);
    }
}
