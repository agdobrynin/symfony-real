<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\DataFixtures\AppFixtures;
use App\DataFixtures\UserFixtureDto;
use App\Entity\Comment;
use App\Entity\MicroPost;
use App\Entity\User;
use App\Helper\FlashType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommentControllerMethodRestoreTest extends WebTestCase
{
    protected const URL_COMMENT_RESTORE_PATTERN = '/micro-post/en/comment/restore/%s';

    /** @var EntityManagerInterface */
    private $em;
    /** @var \Symfony\Contracts\Translation\TranslatorInterface */
    private $translator;
    /** @var \App\Repository\UserRepository */
    private $userRepository;
    /** @var \App\Repository\MicroPostRepository */
    private $microPostRepository;
    /** @var \App\Repository\CommentRepository */
    private $commentRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->translator = self::getContainer()->get(TranslatorInterface::class);
        $this->userRepository = $this->em->getRepository(User::class);
        $this->microPostRepository = $this->em->getRepository(MicroPost::class);
        $this->commentRepository = $this->em->getRepository(Comment::class);

    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }

    public function getSourceData(): \Generator
    {
        $adminFixtureDto = AppFixtures::searchUserFixtureByProperty('isAdmin', true);

        yield 'Success del and restore by user with role ROLE_ADMIN' => [
            $adminFixtureDto
        ];

        $userFixtureDto = AppFixtures::searchUserFixtureByProperty('isAdmin', false);

        yield 'Fail del and restore by user with role ROLE_USER with filter SoftDelete on (default)' => [
            $userFixtureDto
        ];
    }

    /**
     * @dataProvider getSourceData
     */
    public function testRestore(UserFixtureDto $dto): void
    {
        $user = $this->userRepository->findOneBy(['login' => $dto->login]);
        $microPost = $this->microPostRepository->findOneBy([]);

        $content = 'Test content for comment';
        $comment = (new Comment())
            ->setContent($content)
            ->setUser($user)
            ->setPost($microPost)
            // mark is deleted by this field
            ->setDeleteAt(new \DateTime());
        $this->em->persist($comment);
        $this->em->flush();

        self::ensureKernelShutdown();
        $client = static::createClient();

        $client->loginUser($user);
        $uri = sprintf(self::URL_COMMENT_RESTORE_PATTERN, $comment->getUuid());
        $client->request('GET', $uri);

        if (\in_array(User::ROLE_ADMIN, $user->getRoles())) {
            self::assertResponseRedirects();

            $successFlashMessage = self::getContainer()
                ->get(SessionInterface::class)
                ->getFlashBag()
                ->get(FlashType::SUCCESS)[0] ?? '';
            $translateSuccessMessage = $this->translator
                ->trans('micro-post.comments.restore.success_message', ['%content_part%' => $comment->getContent()]);

            self::assertStringStartsWith(mb_substr($successFlashMessage, 0, -3), $translateSuccessMessage);
            $comment = $this->commentRepository->find($comment->getUuid());
            self::assertFalse($comment->isDeleted());
        } else {
            self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        }
    }
}
