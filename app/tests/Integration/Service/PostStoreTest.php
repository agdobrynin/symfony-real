<?php

namespace App\Tests\Integration\Service;

use App\Dto\SimplePostDto;
use App\Service\PostStore;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class PostStoreTest extends KernelTestCase
{
    protected static $postStore;
    protected static $requestStack;

    public static function setUpBeforeClass(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $request = new Request();
        $request->setSession($session);
        $requestStack = new RequestStack();
        $requestStack->push($request);
        self::$requestStack = $requestStack;
        self::$postStore = new PostStore($requestStack, 2);
    }

    public function testConstructor(): void
    {
        $postStore = new PostStore(self::$requestStack, 2);

        self::assertCount(0, $postStore->list(1));
    }

    public function testAddGetPost(): void
    {
        $dto = new SimplePostDto();
        $dto->title = 'Title of post';
        $dto->content = 'Content of post';
        $dto->author = 'Rocket man';

        self::$postStore->add($dto);

        self::assertEquals($dto, self::$postStore->get($dto->uuid));
        self::assertEquals(null, self::$postStore->get('undefined'));
    }

    public function testPageCount(): void
    {
        self::$postStore->setPageSize(2);

        for ($i = 0; $i <= 5; $i++) {
            $dto = new SimplePostDto();
            $dto->title = 'Title ' . $i;
            $dto->title = 'Content ' . $i;

            self::$postStore->add($dto);
        }

        self::assertEquals(4, self::$postStore->getPageCount());
        self::assertCount(1, self::$postStore->list(4));
    }

    public function testDestroy(): void
    {
        $dto = new SimplePostDto();
        $dto->title = 'Title of post';
        $dto->content = 'Content of post';

        self::$postStore->setPageSize(1);
        self::$postStore->add($dto);

        self::assertCount(1, self::$postStore->list(1));
        self::$postStore->destroy();
        self::assertCount(0, self::$postStore->list(1));
    }
}
