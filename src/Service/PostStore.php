<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\SimplePostDto;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PostStore implements PostStoreInterface
{
    private const KEY_POSTS = 'posts';

    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
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
    public function list(): array
    {
        return $this->session->get(self::KEY_POSTS, []);
    }

    public function destroy(): void
    {
        $this->session->remove(self::KEY_POSTS);
    }
}
