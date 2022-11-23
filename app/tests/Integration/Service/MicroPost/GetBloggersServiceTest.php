<?php
declare(strict_types=1);

namespace App\Tests\Integration\Service\MicroPost;

use App\Dto\Exception\PaginatorDtoPageException;
use App\Entity\User;
use App\Service\MicroPost\GetBloggersServiceInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GetBloggersServiceTest extends KernelTestCase
{
    private $pageSize;
    /**
     * @var \Doctrine\Persistence\ObjectManager
     */
    private $em;
    /**
     * @var \App\Repository\UserRepository
     */
    private $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pageSize = self::getContainer()->getParameter('micropost.bloggers.page.size');
        $this->em = self::getContainer()->get('doctrine')->getManager();
        $this->userRepository = $this->em->getRepository(User::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }

    public function sourceData(): \Generator
    {
        yield [1, null];
        yield [10000, PaginatorDtoPageException::class];
    }

    /**
     * @dataProvider sourceData
     */
    public function testX(int $page, ?string $expectException): void
    {
        /*
        $totalItems = $this->userRepository->getCountAll();
        $paginatorDto = new PaginatorDto($page, $totalItems, $this->pageSize);
        $bloggers = $this->userRepository->getByPaginator($paginatorDto);
         */
        /** @var GetBloggersServiceInterface $srv */
        $srv = self::getContainer()->get(GetBloggersServiceInterface::class);

        if ($expectException) {
            self::expectException($expectException);
        }

        // get all bloggers

        $dto = $srv->getBloggers($page);
        self::assertEquals($page, $dto->getPaginatorDto()->getPage());
        self::assertEquals($this->pageSize, $dto->getPaginatorDto()->getPageSize());
    }
}
