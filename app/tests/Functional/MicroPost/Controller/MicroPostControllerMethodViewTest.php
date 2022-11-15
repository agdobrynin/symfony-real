<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\Entity\MicroPost;
use App\Entity\User;
use App\Repository\MicroPostRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class MicroPostControllerMethodViewTest extends WebTestCase
{
    protected const URL_POST_VIEW_PATTERN = '/micro-post/en/view/%s';
    protected const URL_POST_VIEW_BY_USER_PATTERN = '/micro-post/en/user/%s';
    protected const URL_PART_POST_EDIT = '/micro-post/en/edit/';
    protected const URL_PART_POST_DEL = '/micro-post/en/del/';
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var MicroPostRepository
     */
    private $microPostRepository;
    /**
     * @var int
     */
    private $pageSize;

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->microPostRepository = self::getContainer()->get(MicroPostRepository::class);
        $this->pageSize = self::getContainer()->getParameter('micropost.page.size');
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
            '/' . $postUserOwner->getEmoji() . '@.+' . preg_quote($postUserOwner->getNick()) . '/i',
            $cardBodyEl->filter('.card-subtitle')->text()
        );
        self::assertStringContainsString($microPost->getContent(), $cardBodyEl->filter('.card-text')->text());
    }

    public function testViewPostsByNotExistUser(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $user = new User();

        $crawler = $client->request('GET', sprintf(self::URL_POST_VIEW_BY_USER_PATTERN, $user->getUuid()));
        self::assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testViewPostsByUser(): void
    {
        $user = $this->userRepository->findOneBy(['login' => 'blogger']);

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
        $admin = $this->userRepository->findOneBy(['login' => 'admin']);
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

    protected function getUrlToView(MicroPost $microPost): string
    {
        return sprintf(self::URL_POST_VIEW_PATTERN, $microPost->getUuid());
    }
}
