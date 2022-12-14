<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\Comment;
use App\Entity\MicroPost;
use App\Entity\User;
use App\Helper\FlashType;
use App\Tests\Functional\MicroPost\Controller\Utils\UserRandom;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommentControllerMethodDelTest extends WebTestCase
{
    protected const URL_COMMENT_DEL_PATTERN = '/micro-post/en/comment/del/%s';

    /**
     * @var \Doctrine\Persistence\ObjectManager
     */
    private $em;
    /**
     * @var \App\Repository\MicroPostRepository
     */
    private $microPostRepository;
    /**
     * @var \App\Repository\UserRepository
     */
    private $userRepository;
    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = self::getContainer()->get('doctrine')->getManager();
        $this->microPostRepository = $this->em->getRepository(MicroPost::class);
        $this->userRepository = $this->em->getRepository(User::class);
        $this->translator = self::getContainer()->get(TranslatorInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }

    public function testDelNotOwnerFail(): void
    {
        $microPost = $this->microPostRepository->findOneBy([]);
        $requestByUser = UserRandom::minimal();
        $this->em->persist($requestByUser);
        $this->em->flush();

        $commentOwner = $this->userRepository->createQueryBuilder('u')
            ->where('u.roles = :role')->setParameter(':role', User::ROLE_USER)
            ->andWhere('u != :user')->setParameter(':user', $requestByUser)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $comment = $this->makeComment($microPost, $commentOwner);

        self::ensureKernelShutdown();
        $client = static::createClient();

        // Delete comment by not owner comment and has role not User::ROLE_ADMIN
        $client->loginUser($requestByUser);
        $url = sprintf(self::URL_COMMENT_DEL_PATTERN, $comment->getUuid());
        $client->request('GET', $url);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDelOwnerSuccess(): void
    {
        $microPost = $this->microPostRepository->findOneBy([]);
        $requestByUser = $microPost->getUser();
        $commentOwner = $microPost->getUser();

        $comment = $this->makeComment($microPost, $commentOwner);
        self::assertTrue($microPost->getComments()->contains($comment));

        self::ensureKernelShutdown();
        $client = static::createClient();
        // Delete comment by comment owner
        $client->loginUser($requestByUser);
        $client->request('GET', sprintf(self::URL_COMMENT_DEL_PATTERN, $comment->getUuid()));
        self::assertResponseRedirects();
        self::assertFalse($microPost->getComments()->contains($comment));

        $successFlashMessage = self::getContainer()
            ->get(SessionInterface::class)
            ->getFlashBag()
            ->get(FlashType::SUCCESS)[0] ?? '';
        $translateSuccessMessage = $this->translator
            ->trans('micro-post.comments.del.success_message', ['%content_part%' => $comment->getContent()]);

        self::assertStringStartsWith(substr($successFlashMessage, 0, -3), $translateSuccessMessage);
    }

    public function testDelAdminSuccess(): void
    {
        $microPost = $this->microPostRepository->findOneBy([]);
        $adminDto = AppFixtures::searchUserFixtureByProperty('isAdmin', true);
        $requestByUser = $this->userRepository->findOneBy(['login' => $adminDto->login]);
        $userDto = AppFixtures::searchUserFixtureByProperty('isAdmin', false);
        $commentOwner = $this->userRepository->findOneBy(['login' => $userDto->login]);

        $comment = $this->makeComment($microPost, $commentOwner);
        self::assertTrue($microPost->getComments()->contains($comment));

        self::ensureKernelShutdown();
        $client = static::createClient();
        // Delete comment by comment owner
        $client->loginUser($requestByUser);
        $client->request('GET', sprintf(self::URL_COMMENT_DEL_PATTERN, $comment->getUuid()));
        self::assertResponseRedirects();
        self::assertFalse($microPost->getComments()->contains($comment));
    }

    public function testDelNotFoundParamConverter(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $requestByUser = $this->userRepository->findOneBy([]);
        $client->loginUser($requestByUser);
        $client->request('GET', sprintf(self::URL_COMMENT_DEL_PATTERN, 'abc-abc-abc'));
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDelSoftDeleteComment(): void
    {
        $microPost = $this->microPostRepository->findOneBy([]);
        $adminDto = AppFixtures::searchUserFixtureByProperty('isAdmin', true);
        $requestByUser = $this->userRepository->findOneBy(['login' => $adminDto->login]);
        $commentOwner = $microPost->getUser();

        $comment = $this->makeComment($microPost, $commentOwner);
        $comment->setDeleteAt(new \DateTime());
        $this->em->persist($comment);
        $this->em->flush();

        self::ensureKernelShutdown();
        $client = static::createClient();
        $client->loginUser($requestByUser);
        $client->request('GET', sprintf(self::URL_COMMENT_DEL_PATTERN, $comment->getUuid()));
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    protected function makeComment(MicroPost $microPost, User $commentOwner): Comment
    {
        $comment = (new Comment())
            ->setPost($microPost)
            ->setUser($commentOwner)
            ->setContent('Lorem ipsum dolor sit amet, consectetur adipisicing elit');

        $this->em->persist($comment);
        $this->em->flush();

        return $comment;
    }
}
