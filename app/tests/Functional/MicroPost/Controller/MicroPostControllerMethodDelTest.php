<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\MicroPost;
use App\Entity\User;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class MicroPostControllerMethodDelTest extends WebTestCase
{
    protected const URL_POST_DEL_PATTERN = '/micro-post/en/del/%s';
    /** @var \App\Repository\UserRepository */
    private $userRepository;
    /** @var \App\Repository\MicroPostRepository */
    private $microPostRepository;
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
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }

    public function testDelPostNonAuthUser(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        // ⚠ All fixtures users with login, microposts defined in App\DataFixtures\AppFixtures
        $microPost = $this->microPostRepository->findOneBy([]);

        $client->request('GET', $this->getUrlToDel($microPost));
        self::assertResponseRedirects();
        $crawler = $client->followRedirect();
        self::assertStringEndsWith('/micro-post/en/login', $crawler->getUri());
    }

    public function testDelPostNotOwnerAndNotAdmin(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        // ⚠ All fixtures users with login, microposts defined in App\DataFixtures\AppFixtures
        $dto = AppFixtures::searchUserFixtureByProperty('isAdmin', false);
        $userBlogger = $this->userRepository->findOneBy(['login' => $dto->login]);
        $microPostOfBlogger = $this->microPostRepository->findOneBy(['user' => $userBlogger]);

        // Find one user who has login not equal 'blogger' and roles not ROLE_ADMIN.
        $criteria = (new Criteria())->where(Criteria::expr()->neq('login', $userBlogger->getLogin()))
            ->andWhere(Criteria::expr()->notIn('roles', [User::ROLE_ADMIN]));
        $userNotOwner = $this->userRepository->matching($criteria)->first();

        $client->loginUser($userNotOwner);
        $client->request('GET', $this->getUrlToDel($microPostOfBlogger));
        self::assertEquals(Response::HTTP_FORBIDDEN, $client->getResponse()->getStatusCode());
    }

    public function testDelPostNotOwnerByAdmin(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        // ⚠ All fixtures users with login, microposts defined in App\DataFixtures\AppFixtures
        $userDto = AppFixtures::searchUserFixtureByProperty('isAdmin', false);
        $userBlogger = $this->userRepository->findOneBy(['login' => $userDto->login]);
        $microPostOfBlogger = $this->microPostRepository->findOneBy(['user' => $userBlogger]);
        $adminDto = AppFixtures::searchUserFixtureByProperty('isAdmin', true);
        $userAdmin = $this->userRepository->findOneBy(['login' => $adminDto->login]);

        $client->loginUser($userAdmin);
        $client->request('GET', $this->getUrlToDel($microPostOfBlogger));
        self::assertResponseRedirects();

        self::assertNull($this->microPostRepository->findOneBy(['uuid' => $microPostOfBlogger->getUuid()]));
    }

    public function testDelPostNonExistPost(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        // ⚠ All fixtures users with login, microposts defined in App\DataFixtures\AppFixtures
        $userDto = AppFixtures::searchUserFixtureByProperty('isAdmin', false);
        $userBlogger = $this->userRepository->findOneBy(['login' => $userDto->login]);
        $microPostOfBlogger = new MicroPost();

        $client->loginUser($userBlogger);
        $client->request('GET', $this->getUrlToDel($microPostOfBlogger));
        self::assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }

    protected function getUrlToDel(MicroPost $microPost): string
    {
        return sprintf(self::URL_POST_DEL_PATTERN, $microPost->getUuid());
    }
}
