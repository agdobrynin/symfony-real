<?php
declare(strict_types=1);

namespace App\Tests\Integration\Service\MicroPost;

use App\Dto\Exception\PaginatorDtoPageException;
use App\Service\MicroPost\GetBloggersServiceInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GetBloggersServiceTest extends KernelTestCase
{
    private $pageSize;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pageSize = self::getContainer()->getParameter('micropost.bloggers.page.size');
    }

    public function sourceData(): \Generator
    {
        yield 'success for page 1' => [1, null];
        yield 'fail for page 10000' => [10000, PaginatorDtoPageException::class];
    }

    /**
     * @dataProvider sourceData
     */
    public function testGetBloggersService(int $page, ?string $expectException): void
    {
        /** @var GetBloggersServiceInterface $srv */
        $srv = self::getContainer()->get(GetBloggersServiceInterface::class);

        if ($expectException) {
            self::expectException($expectException);
        }

        $dto = $srv->getBloggers($page);

        self::assertLessThanOrEqual($this->pageSize, \count($dto->getBloggers()));
        self::assertEquals($page, $dto->getPaginatorDto()->getPage());
        self::assertEquals($this->pageSize, $dto->getPaginatorDto()->getPageSize());
    }
}
