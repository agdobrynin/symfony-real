<?php
declare(strict_types=1);

namespace App\Tests\Integration\Service\MicroPost;

use App\Dto\Exception\PaginatorDtoPageException;
use App\Dto\Exception\PaginatorDtoPageSizeException;
use App\Entity\Comment;
use App\Entity\MicroPost;
use App\Repository\CommentRepository;
use App\Service\MicroPost\GetMicroPostCommentsService;
use App\Service\MicroPost\GetMicroPostCommentsServiceInterface;
use Closure;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GetMicroPostCommentsServiceTest extends KernelTestCase
{
    /**
     * @var int
     */
    private $pageSize;
    /**
     * @var \Doctrine\Persistence\ObjectManager
     */
    private $em;
    /**
     * @var \App\Repository\MicroPostRepository
     */
    private $microPostRepository;
    /** @var \App\Repository\CommentRepository */
    private $commentsRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pageSize = self::getContainer()->getParameter('micropost.comments.page.size');
        $this->em = self::getContainer()->get('doctrine')->getManager();
        $this->microPostRepository = $this->em->getRepository(MicroPost::class);
        $this->commentsRepository = $this->em->getRepository(Comment::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }

    public function testGetMicroPostCommentsService(): void
    {
        $srv = self::getContainer()->get(GetMicroPostCommentsServiceInterface::class);
        $microPost = $this->microPostRepository->findOneBy([]);
        $this->clearComments($microPost);

        // Generate comments for 2 pages.
        $expectCommentCount = $this->pageSize + 1;
        $this->addComment($microPost, $expectCommentCount, function () {
            $this->em->flush();
        });
        $this->em->refresh($microPost);

        self::assertEquals($expectCommentCount, $microPost->getComments()->count());

        $firstPage = $srv->getComments(1, $microPost);
        self::assertEquals(1, $firstPage->getPaginatorDto()->getPage());
        self::assertEquals(2, $firstPage->getPaginatorDto()->getTotalPages());
        self::assertEquals(0, $firstPage->getPaginatorDto()->getFirstResultIndex());
        self::assertEquals($this->pageSize, $firstPage->getPaginatorDto()->getPageSize());
        self::assertCount($this->pageSize, $firstPage->getComments());
    }

    public function testWrongPageSize(): void
    {
        $srv = new GetMicroPostCommentsService(0, $this->commentsRepository);
        $microPost = $this->microPostRepository->findOneBy([]);
        $this->clearComments($microPost);
        self::expectException(PaginatorDtoPageSizeException::class);
        $srv->getComments(1, $microPost);
    }

    public function testWrongPage(): void
    {
        $srv = self::getContainer()->get(GetMicroPostCommentsServiceInterface::class);
        $microPost = $this->microPostRepository->findOneBy([]);
        $this->clearComments($microPost);

        $this->addComment($microPost, 1, function () {
            $this->em->flush();
        });
        $this->em->refresh($microPost);

        self::expectException(PaginatorDtoPageException::class);
        $srv->getComments(1000, $microPost)->getComments();
    }

    protected function clearComments(MicroPost $microPost): void
    {
        /** @var CommentRepository $repo */
        $repo = $this->em->getRepository(Comment::class);
        $repo->createQueryBuilder('c')->delete()
            ->where('c.post = :micropost')
            ->setParameter(':micropost', $microPost)
            ->getQuery()
            ->execute();
        $this->em->refresh($microPost);
    }

    /**
     * @param MicroPost $microPost
     * @param Closure $closure
     * @return void
     */
    protected function addComment(MicroPost $microPost, int $maxComment, Closure $closure)
    {
        for ($i = 0; $i < $maxComment; $i++) {
            $comment = (new Comment())
                ->setUser($microPost->getUser())
                ->setPost($microPost)
                ->setContent(sprintf('Comment index %s in post %s', $i, $microPost->getUuid()));
            $this->em->persist($comment);
        }

        $closure();
    }
}
