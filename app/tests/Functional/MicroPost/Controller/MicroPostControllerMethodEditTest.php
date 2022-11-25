<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\Entity\MicroPost;
use App\Entity\User;
use App\Tests\Functional\MicroPost\Controller\Utils\MicroPostFormTrait;
use Doctrine\Common\Collections\Criteria;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Response;

class MicroPostControllerMethodEditTest extends WebTestCase
{
    use MicroPostFormTrait;

    protected const URL_POST_EDIT_PATTERN = '/micro-post/en/edit/%s';
    /** @var \App\Repository\UserRepository */
    private $userRepository;
    /** @var \App\Repository\MicroPostRepository */
    private $microPostRepository;
    /** @var \Doctrine\Persistence\ObjectManager */
    private $em;
    /**
     * @var \Faker\Generator
     */
    private $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = self::getContainer()->get('doctrine')->getManager();
        $this->userRepository = $this->em->getRepository(User::class);
        $this->microPostRepository = $this->em->getRepository(MicroPost::class);
        $this->faker = Factory::create();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }

    public function testOpenEditPostFormNotAuthUser(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        // ⚠ All fixtures users with login, microposts defined in App\DataFixtures\AppFixtures
        $user = $this->userRepository->findOneBy(['login' => 'blogger']);
        $microPost = $this->microPostRepository->findOneBy(['user' => $user]);

        $client->request('GET', $this->getUrlToEdit($microPost));
        self::assertResponseRedirects();
        $crawler = $client->followRedirect();
        self::assertStringEndsWith('/micro-post/en/login', $crawler->getUri());
    }

    public function testOpenEditPostFormNotOwnerAndNotAdmin(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        // ⚠ All fixtures users with login, microposts defined in App\DataFixtures\AppFixtures
        $userBlogger = $this->userRepository->findOneBy(['login' => 'blogger']);
        $microPostOfBlogger = $this->microPostRepository->findOneBy(['user' => $userBlogger]);

        // Find one user who has login not equal 'blogger' and roles not ROLE_ADMIN.
        $criteria = (new Criteria())->where(Criteria::expr()->neq('login', $userBlogger->getLogin()))
            ->andWhere(Criteria::expr()->notIn('roles', [User::ROLE_ADMIN]));
        $userNotOwner = $this->userRepository->matching($criteria)->first();

        $client->loginUser($userNotOwner);
        $client->request('GET', $this->getUrlToEdit($microPostOfBlogger));
        self::assertEquals(Response::HTTP_FORBIDDEN, $client->getResponse()->getStatusCode());
    }

    public function testOpenEditPostFormNotOwnerByAdmin(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        // ⚠ All fixtures users with login, microposts defined in App\DataFixtures\AppFixtures
        $userBlogger = $this->userRepository->findOneBy(['login' => 'blogger']);
        $microPostOfBlogger = $this->microPostRepository->findOneBy(['user' => $userBlogger]);

        $userAdmin = $this->userRepository->findOneBy(['login' => 'admin']);

        $client->loginUser($userAdmin);
        $crawler = $client->request('GET', $this->getUrlToEdit($microPostOfBlogger));
        self::assertResponseIsSuccessful();

        $contentForPost = $this->faker->realTextBetween(278, 280);
        $form = self::getFormWithData($crawler, $contentForPost);
        self::assertInstanceOf(Form::class, $form);

        $client->submit($form);
        self::assertResponseRedirects();
        $client->followRedirect();
        $this->em->refresh($microPostOfBlogger);

        self::assertEquals($contentForPost, $microPostOfBlogger->getContent());
    }

    public function testOpenEditPostFormByOwner(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        // ⚠ All fixtures users with login, microposts defined in App\DataFixtures\AppFixtures
        $userBlogger = $this->userRepository->findOneBy(['login' => 'blogger']);
        $microPostOfBlogger = $this->microPostRepository->findOneBy(['user' => $userBlogger]);

        $client->loginUser($userBlogger);
        $crawler = $client->request('GET', $this->getUrlToEdit($microPostOfBlogger));
        self::assertResponseIsSuccessful();

        $contentForPost = $this->faker->realTextBetween(278, 280);
        $form = self::getFormWithData($crawler, $contentForPost);
        self::assertInstanceOf(Form::class, $form);

        $client->submit($form);
        self::assertResponseRedirects();
        $client->followRedirect();
        $this->em->refresh($microPostOfBlogger);

        self::assertEquals($contentForPost, $microPostOfBlogger->getContent());
    }

    public function testOpenEditPostFormNonExistPost(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        // ⚠ All fixtures users with login, microposts defined in App\DataFixtures\AppFixtures
        $userBlogger = $this->userRepository->findOneBy(['login' => 'blogger']);
        $microPostOfBlogger = new MicroPost();

        $client->loginUser($userBlogger);
        $client->request('GET', $this->getUrlToEdit($microPostOfBlogger));
        self::assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }

    protected function getUrlToEdit(MicroPost $microPost): string
    {
        return sprintf(self::URL_POST_EDIT_PATTERN, $microPost->getUuid());
    }
}
