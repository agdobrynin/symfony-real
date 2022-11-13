<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\Entity\MicroPost;
use App\Entity\User;
use App\Repository\MicroPostRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class MicroPostControllerMethodEditTest extends WebTestCase
{
    protected const URL_POST_EDIT_PATTERN = '/micro-post/en/edit/%s';
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var MicroPostRepository
     */
    private $microPostRepository;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var \Faker\Generator
     */
    private $faker;

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->microPostRepository = self::getContainer()->get(MicroPostRepository::class);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->faker = Factory::create();
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

        $contentEl = $crawler->filter('textarea[name$="[content]"]');
        $contentForPost = $this->faker->realTextBetween(278, 280);
        $form = $contentEl->closest('form')->form([
            $contentEl->attr('name') => $contentForPost,
        ]);

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

        $contentEl = $crawler->filter('textarea[name$="[content]"]');
        $contentForPost = $this->faker->realTextBetween(278, 280);
        $form = $contentEl->closest('form')->form([
            $contentEl->attr('name') => $contentForPost,
        ]);

        $client->submit($form);
        self::assertResponseRedirects();
        $client->followRedirect();
        $this->em->refresh($microPostOfBlogger);

        self::assertEquals($contentForPost, $microPostOfBlogger->getContent());
    }

    protected function getUrlToEdit(MicroPost $microPost): string
    {
        return sprintf(self::URL_POST_EDIT_PATTERN, $microPost->getUuid());
    }
}
