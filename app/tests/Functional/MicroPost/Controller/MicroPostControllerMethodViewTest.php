<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\MicroPost;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class MicroPostControllerMethodViewTest extends WebTestCase
{
    protected const URL_POST_VIEW_PATTERN = '/micro-post/en/view/%s';
    protected const URL_POST_VIEW_BY_USER_PATTERN = '/micro-post/en/user/%s';
    protected const URL_PART_POST_EDIT = '/micro-post/en/edit/';
    protected const URL_PART_POST_DEL = '/micro-post/en/del/';
    /**
     * @var \App\Repository\UserRepository
     */
    private $userRepository;
    /**
     * @var \App\Repository\MicroPostRepository
     */
    private $microPostRepository;
    /**
     * @var int
     */
    private $pageSize;
    /**
     * @var \Doctrine\Persistence\ObjectManager
     */
    private $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = self::getContainer()->get('doctrine')->getManager();
        $this->userRepository = $this->em->getRepository(User::class);
        $this->microPostRepository = $this->em->getRepository(MicroPost::class);
        $this->pageSize = self::getContainer()->getParameter('micropost.page.size');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }

    public function testViewPostNonExist(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $microPost = new MicroPost();

        $client->request('GET', $this->getUrlToView($microPost));
        self::assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testViewPost(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        /** @var MicroPost $microPost */
        $microPost = $this->microPostRepository->createQueryBuilder('mp')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        $crawler = $client->request('GET', $this->getUrlToView($microPost));
        self::assertResponseIsSuccessful();

        $postUserOwner = $microPost->getUser();
        $cardBodyEl = $crawler->filter('main .card .card-body')->first();
        self::assertMatchesRegularExpression(
            '/' . $postUserOwner->getEmoji() . '@' . preg_quote($postUserOwner->getNick()) . '/i',
            $cardBodyEl->filter('.card-subtitle')->text()
        );
        self::assertStringContainsString($microPost->getContent(), $cardBodyEl->filter('.card-text')->text());
    }

    public function testViewPostsByNotExistUser(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $user = new User();

        $client->request('GET', sprintf(self::URL_POST_VIEW_BY_USER_PATTERN, $user->getUuid()));
        self::assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testViewPostsByUser(): void
    {
        $userDto = AppFixtures::searchUserFixtureByProperty('isAdmin', false);
        $user = $this->userRepository->findOneBy(['login' => $userDto->login]);

        self::ensureKernelShutdown();
        $client = static::createClient();
        $crawler = $client->request('GET', sprintf(self::URL_POST_VIEW_BY_USER_PATTERN, $user->getUuid()));
        self::assertResponseIsSuccessful();

        // Page has pagination function, if all posts more the post per page set true value for expect value
        $expectPostsCount = $user->getPosts()->count() > $this->pageSize ? $this->pageSize : $user->getPosts()->count();

        self::assertEquals($expectPostsCount, $crawler->filter('main .post-item')->count());

        // request was sent as anonymous - button edit, delete not available on page.
        foreach ([self::URL_PART_POST_DEL, self::URL_PART_POST_EDIT] as $urlPart) {
            self::assertEquals(
                0,
                $crawler->filter('main .post-item a[href*="' . $urlPart . '"]')->count()
            );
        }
        // make request as ADMIN - buttons del, edit at each post available.
        $adminDto = AppFixtures::searchUserFixtureByProperty('isAdmin', true);
        $admin = $this->userRepository->findOneBy(['login' => $adminDto->login]);
        $client->loginUser($admin);
        $crawler = $client->request('GET', sprintf(self::URL_POST_VIEW_BY_USER_PATTERN, $user->getUuid()));
        self::assertResponseIsSuccessful();
        // request was sent as anonymous - button edit, delete not available on page.
        foreach ([self::URL_PART_POST_DEL, self::URL_PART_POST_EDIT] as $urlPart) {
            self::assertEquals(
                $expectPostsCount,
                $crawler->filter('main .post-item a[href*="' . $urlPart . '"]')->count()
            );
        }

    }

    public function testViewPostsByUserWithWrongPageForPostPagination(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $user = $this->userRepository->findOneBy([]);

        foreach ([0, -1, 'abc', 10000] as $page) {
            $uri = sprintf(self::URL_POST_VIEW_BY_USER_PATTERN, $user->getUuid()) . '?page=' . $page;
            $client->request('GET', $uri);
            self::assertResponseIsUnprocessable();
        }
    }

    protected function getUrlToView(MicroPost $microPost): string
    {
        return sprintf(self::URL_POST_VIEW_PATTERN, $microPost->getUuid());
    }
}
