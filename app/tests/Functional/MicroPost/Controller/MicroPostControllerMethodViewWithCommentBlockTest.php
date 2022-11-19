<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\Entity\MicroPost;
use App\Service\MicroPost\GetMicroPostCommentsService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Translation\TranslatorInterface;

class MicroPostControllerMethodViewWithCommentBlockTest extends WebTestCase
{
    protected const URL_POST_VIEW_PATTERN = '/micro-post/en/view/%s';

    /**
     * @var \Doctrine\Persistence\ObjectManager
     */
    private $em;
    /**
     * @var \App\Repository\MicroPostRepository
     */
    private $microPostRepository;
    /**
     * @var int
     */
    private $commentPageSize;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = self::getContainer()->get('doctrine')->getManager();
        $this->microPostRepository = $this->em->getRepository(MicroPost::class);
        $this->commentPageSize = self::getContainer()->getParameter('micropost.comments.page.size');
        $this->translator = self::getContainer()->get(TranslatorInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }

    public function testCommentBlockFormNotFound(): void
    {
        self::ensureKernelShutdown();
        $microPost = $this->microPostRepository->findOneBy([]);

        $client = static::createClient();
        // Send request not auth user
        $crawler = $client->request('GET', sprintf(self::URL_POST_VIEW_PATTERN, $microPost->getUuid()));
        self::assertResponseIsSuccessful();

        // Form for send comment must be not found because user request non auth.
        self::assertCount(0, $this->getCommentForm($crawler));
        // Alert block with info text
        $alertText = $this->translator->trans('micro-post.comments.comment_for_auth');
        self::assertEquals($alertText, $crawler->filter('.comment-for-auth')->first()->text());
    }

    public function testCommentBlockShow(): void
    {
        self::ensureKernelShutdown();
        $microPost = $this->microPostRepository->findOneBy([]);

        $client = static::createClient();
        $client->loginUser($microPost->getUser());

        $crawler = $client->request('GET', sprintf(self::URL_POST_VIEW_PATTERN, $microPost->getUuid()));
        self::assertResponseIsSuccessful();

        // Find comments with cssClass = comment-item
        $getMicroPostCommentsService = new GetMicroPostCommentsService($this->commentPageSize);
        $microPostCommentsWithPaginatorDto = $getMicroPostCommentsService->getComments(1, $microPost);
        self::assertCount($crawler->filter('div.comment-item')->count(), $microPostCommentsWithPaginatorDto->getComments());

        // Form for send comment must be found
        self::assertCount(1, $this->getCommentForm($crawler));
    }

    public function testCommentBlockAddWrong(): void
    {
        self::ensureKernelShutdown();
        $microPost = $this->microPostRepository->findOneBy([]);

        $client = static::createClient();
        $client->loginUser($microPost->getUser());
        $crawler = $client->request('GET', sprintf(self::URL_POST_VIEW_PATTERN, $microPost->getUuid()));
        self::assertResponseIsSuccessful();

        // Form for send comment must be found
        $formComment = $this->getCommentForm($crawler);
        self::assertCount(1, $formComment);

        // Send wrong length comment
        $form = $formComment->filter('button[name="comment_form[save]"]')->form([
            $formComment->filter('textarea')->first()->attr('name') => 'short'
        ]);

        $crawler = $client->submit($form);
        self::assertResponseIsUnprocessable();
        $textAreaField = $this->getCommentForm($crawler)->filter('textarea.is-invalid');
        self::assertCount(1, $textAreaField);
        self::assertNotEmpty($textAreaField->nextAll()->first()->filter('.invalid-feedback')->text());
    }

    public function testCommentBlockAddSuccess(): void
    {
        self::ensureKernelShutdown();
        $microPost = $this->microPostRepository->findOneBy([]);

        $client = static::createClient();
        $client->loginUser($microPost->getUser());
        $crawler = $client->request('GET', sprintf(self::URL_POST_VIEW_PATTERN, $microPost->getUuid()));
        self::assertResponseIsSuccessful();

        $formComment = $this->getCommentForm($crawler);

        // Send comment
        $commentContentSend = 'Lorem ipsum dolor sit amet.';
        $form = $formComment->filter('button[name="comment_form[save]"]')->form([
            $formComment->filter('textarea')->first()->attr('name') => $commentContentSend
        ]);

        $client->submit($form);
        self::assertResponseRedirects();
        $crawler = $client->followRedirect();

        $firstCommentBlock = $crawler->filter('div.comment-item')->first();
        $commentTextOnPage = $firstCommentBlock->filter('.card-text')->first()->text();
        self::assertEquals($commentContentSend, $commentTextOnPage);
        $headerOfComment = $firstCommentBlock->filter('.card-header')->first()->text();
        $user = $microPost->getUser();
        self::assertStringContainsString($user->getEmoji() . '@' . $user->getNick(), $headerOfComment);
    }

    protected function getCommentForm(Crawler $crawler)
    {
        return $crawler->filter('form[name="comment_form"]');
    }
}
