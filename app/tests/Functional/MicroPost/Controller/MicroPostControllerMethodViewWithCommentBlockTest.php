<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\Entity\MicroPost;
use App\Service\MicroPost\GetMicroPostCommentsService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Field\FormField;
use Symfony\Component\DomCrawler\Form;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Symfony\Component\String\u;

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

        self::assertNull($this->getFormComment($crawler));

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
        self::assertInstanceOf(Form::class, $this->getFormComment($crawler));
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
        $form = $this->getFormComment($crawler);
        self::assertInstanceOf(Form::class, $form);

        $data = $this->getFormData($form, 'short');

        $crawler = $client->submit($form, $data);
        self::assertResponseIsUnprocessable();

        $cssSelectorTextarea = sprintf('form[name="%s"] textarea.is-invalid',
            $this->getFormComment($crawler)->getName());

        $textareaField = $crawler->filter($cssSelectorTextarea);
        self::assertCount(1, $textareaField);
        self::assertNotEmpty($textareaField->nextAll()->first()->filter('.invalid-feedback')->text());
    }

    public function testCommentBlockAddSuccess(): void
    {
        self::ensureKernelShutdown();
        $microPost = $this->microPostRepository->findOneBy([]);

        $client = static::createClient();
        $client->loginUser($microPost->getUser());
        $crawler = $client->request('GET', sprintf(self::URL_POST_VIEW_PATTERN, $microPost->getUuid()));
        self::assertResponseIsSuccessful();

        // Form for send comment must be found
        $form = $this->getFormComment($crawler);
        self::assertInstanceOf(Form::class, $form);

        $commentContentSend = 'Lorem ipsum dolor sit amet.';
        $data = $this->getFormData($form, $commentContentSend);

        $client->submit($form, $data);
        self::assertResponseRedirects();

        $crawler = $client->followRedirect();

        $firstCommentBlock = $crawler->filter('div.comment-item')->first();
        $commentTextOnPage = $firstCommentBlock->filter('.card-text')->first()->text();
        self::assertEquals($commentContentSend, $commentTextOnPage);
        $headerOfComment = $firstCommentBlock->filter('.card-header')->first()->text();
        $user = $microPost->getUser();
        self::assertStringContainsString($user->getEmoji() . '@' . $user->getNick(), $headerOfComment);
    }

    protected function getFormCommentSubmitButtonEl(Crawler $crawler)
    {
        return $crawler->filter('form button[name$="[save]"]');
    }

    protected function getFormComment(Crawler $crawler): ?\Symfony\Component\DomCrawler\Form
    {
        try {
            return $this->getFormCommentSubmitButtonEl($crawler)->form();
        } catch (\InvalidArgumentException $exception) {
            return null;
        }
    }

    protected function getFormData(Form $form, string $content): array
    {
        return array_reduce($form->all(), static function (array $acc, FormField $field) use ($content): array {
            if (u($field->getName())->endsWith('[content]')) {
                $acc [$field->getName()] = $content;
            }

            return $acc;
        }, []);
    }
}
