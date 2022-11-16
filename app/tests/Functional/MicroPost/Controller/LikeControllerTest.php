<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\Entity\MicroPost;
use App\Entity\User;
use App\Repository\MicroPostRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class LikeControllerTest extends WebTestCase
{
    use MailerAssertionsTrait;

    protected const URL_LIKE_EN_POST_PATTERN = '/micro-post/en/like/%s';
    protected const URL_UNLIKE_EN_POST_PATTERN = '/micro-post/en/unlike/%s';
    protected const URL_LINK_TO_POST_EMAIL_PATTERN = '/micro-post/%s/view/%s';
    protected const URL_LINK_TO_USER_EMAIL_PATTERN = '/micro-post/%s/user/%s';
    protected const URL_REGISTER_EN = '/micro-post/en/register';

    /** @var MicroPostRepository */
    protected $microPostRepository;
    /** @var UserRepository */
    protected $userRepository;
    /** @var ObjectManager */
    protected $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = self::getContainer()->get('doctrine')->getManager();
        $this->microPostRepository = $this->em->getRepository(MicroPost::class);
        $this->userRepository = $this->em->getRepository(User::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }

    public function testLikePostNotAuthUser(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $userAdmin = $this->userRepository->findOneBy(['login' => 'admin']);
        $microPost = $this->microPostRepository->findOneBy(['user' => $userAdmin]);

        $client->request('GET', sprintf(self::URL_LIKE_EN_POST_PATTERN, $microPost->getUuid()));
        $response = $client->getResponse();

        self::assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertEquals('application/json', $response->headers->get('content-type'));
        $json = json_decode($client->getResponse()->getContent());
        self::assertEquals(self::URL_REGISTER_EN, $json->redirect);
    }

    public function testLikePostSuccess(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $userAdmin = $this->userRepository->findOneBy(['login' => 'admin']);
        $userBlogger = $this->userRepository->findOneBy(['login' => 'blogger']);

        $microPost = $this->microPostRepository->findOneBy(['user' => $userAdmin]);
        self::assertCount(0, $microPost->getLikedBy());

        $client->loginUser($userBlogger);
        $client->request('GET', sprintf(self::URL_LIKE_EN_POST_PATTERN, $microPost->getUuid()));
        self::assertResponseIsSuccessful();

        $this->em->refresh($microPost);

        // Test response and json answer.
        $this->assertJsonResponse($client->getResponse(), $microPost);

        // After like post by user, we send email to post owner
        $email = self::getMailerMessage();
        $linkToPost = sprintf(
            self::URL_LINK_TO_POST_EMAIL_PATTERN,
            $microPost->getUser()->getPreferences()->getLocale(), $microPost->getUuid());
        $linkToUserWhoLike = sprintf(
            self::URL_LINK_TO_USER_EMAIL_PATTERN,
            $microPost->getUser()->getPreferences()->getLocale(), $userBlogger->getUuid());

        self::assertEmailHtmlBodyContains($email, $linkToPost);
        self::assertEmailHtmlBodyContains($email, $linkToUserWhoLike);
        self::assertEmailTextBodyContains($email, $linkToPost);
        self::assertEmailTextBodyContains($email, $linkToUserWhoLike);

        $emailAsString = $email->toString();
        $headerMailToForRegExp = '/To:.*' . preg_quote($microPost->getUser()->getEmail()) . '/i';
        self::assertMatchesRegularExpression($headerMailToForRegExp, $emailAsString);

        $adminEmail = self::getContainer()->getParameter('micropost.admin.email');
        $headerMailFromForRegExp = '/From:.*' . preg_quote($adminEmail) . '/i';
        self::assertMatchesRegularExpression($headerMailFromForRegExp, $emailAsString);

        self::assertTrue($microPost->getLikedBy()->contains($userBlogger));
    }

    public function testUnlikePostSuccess(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $userAdmin = $this->userRepository->findOneBy(['login' => 'admin']);
        $userBlogger = $this->userRepository->findOneBy(['login' => 'blogger']);

        $microPost = $this->microPostRepository->findOneBy(['user' => $userAdmin]);
        $microPost->like($userBlogger);
        $this->em->persist($microPost);
        $this->em->flush();

        $this->em->refresh($microPost);
        self::assertTrue($microPost->getLikedBy()->contains($userBlogger));

        $client->loginUser($userBlogger);
        $client->request('GET', sprintf(self::URL_UNLIKE_EN_POST_PATTERN, $microPost->getUuid()));
        self::assertResponseIsSuccessful();

        $this->em->refresh($microPost);
        self::assertFalse($microPost->getLikedBy()->contains($userBlogger));

        $this->assertJsonResponse($client->getResponse(), $microPost);
    }

    protected function assertJsonResponse(Response $response, MicroPost $microPost): void
    {
        // Test response and json answer.
        self::assertEquals('application/json', $response->headers->get('content-type'));
        $json = json_decode($response->getContent());
        self::assertCount($json->count, $microPost->getLikedBy());
    }
}
