<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\Entity\MicroPost;
use App\Entity\User;
use App\Repository\MicroPostRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class MicroPostControllerMethodDelTest extends WebTestCase
{
    protected const URL_POST_DEL_PATTERN = '/micro-post/en/del/%s';
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

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->microPostRepository = self::getContainer()->get(MicroPostRepository::class);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testDelPostNonAuthUser(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        // ⚠ All fixtures users with login, microposts defined in App\DataFixtures\AppFixtures
        $microPost = $this->microPostRepository->createQueryBuilder('mp')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

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
        $userBlogger = $this->userRepository->findOneBy(['login' => 'blogger']);
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
        $userBlogger = $this->userRepository->findOneBy(['login' => 'blogger']);
        $microPostOfBlogger = $this->microPostRepository->findOneBy(['user' => $userBlogger]);
        $userAdmin = $this->userRepository->findOneBy(['login' => 'admin']);

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
        $userBlogger = $this->userRepository->findOneBy(['login' => 'blogger']);
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
