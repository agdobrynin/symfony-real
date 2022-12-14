<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\Entity\MicroPost;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class MicroPostControllerMethodIndexTest extends WebTestCase
{
    /** @var \Doctrine\Persistence\ObjectManager */
    private $em;
    /** @var \App\Repository\MicroPostRepository */
    private $microPostRepository;
    /** @var int */
    private $pageSize;
    /** @var \App\Repository\UserRepository */
    private $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = self::getContainer()->get('doctrine')->getManager();
        $this->microPostRepository = $this->em->getRepository(MicroPost::class);
        $this->userRepository = $this->em->getRepository(User::class);
        $this->pageSize = self::getContainer()->getParameter('micropost.page.size');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }

    public function testIndexNonAuthUserPagination(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $crawler = $client->request('GET', '/micro-post/ru/');
        self::assertResponseIsSuccessful();

        $paginatorItems = $this->getPaginatorItemByIndex($crawler);

        //I'm on first page, check it.
        $firstPageItem = $paginatorItems->first();
        self::assertEquals('#', $firstPageItem->attr("href"));
        self::assertEquals('1', $firstPageItem->text());
        // goto page 2
        $link = $paginatorItems->selectLink('2')->link();
        $crawler = $client->click($link);
        self::assertResponseIsSuccessful();
        // selected page
        $secondPageItem = $this->getPaginatorItemByIndex($crawler, 1);
        self::assertEquals('#', $secondPageItem->attr("href"));
        self::assertEquals('2', $secondPageItem->text());
        // first page
        $firstPageItem = $this->getPaginatorItemByIndex($crawler, 0);
        self::assertStringContainsString('/?page=1', $firstPageItem->attr("href"));
        self::assertEquals('1', $firstPageItem->text());
    }

    public function testIndexWrongPage(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        foreach ([50000, 0, -1, 'abc'] as $page) {
            $client->request('GET', '/micro-post/ru/?page=' . $page);
            self::assertResponseIsUnprocessable();
        }
    }

    public function testAuthUserIndexWrongPage(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $user = $this->userRepository->findOneBy([]);
        $client->loginUser($user);

        foreach ([5000, 0, -1, 'abc'] as $page) {
            $client->request('GET', '/micro-post/ru/?page=' . $page);
            self::assertResponseIsUnprocessable();
        }
    }

    protected function getPaginatorItemByIndex(Crawler $crawler, ?int $index = null): Crawler
    {
        $paginatorItems = $crawler->filter('ul.pagination')
            ->first()
            ->filter('li.page-item > a.page-link');

        return is_null($index) ? $paginatorItems : $paginatorItems->eq($index);
    }
}
