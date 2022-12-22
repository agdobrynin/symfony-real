<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\MicroPost;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\UuidV4;
use Symfony\Contracts\Translation\TranslatorInterface;

class SoftDeletedPostsControllerTest extends WebTestCase
{
    private const URL_EN_DELETED_MICROPOST = '/micro-post/en/trash_bin/list';
    private const URL_EN_RESTORE_MICROPOST = '/micro-post/en/trash_bin/restore/%s';
    private const URL_EN_VIEW_MICROPOST = '/micro-post/en/view/%s';

    /** @var \Doctrine\Persistence\ObjectManager */
    private $em;
    /** @var \App\Repository\UserRepository */
    private $userRepository;
    /** @var \App\Repository\MicroPostRepository */
    private $microPostRepository;
    /** @var \Symfony\Component\Translation\IdentityTranslator */
    private $translator;
    /** @var User */
    private $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = self::getContainer()->get('doctrine')->getManager();
        $this->userRepository = $this->em->getRepository(User::class);
        $this->microPostRepository = $this->em->getRepository(MicroPost::class);
        $this->translator = self::getContainer()->get(TranslatorInterface::class);

        $userFixtureDto = AppFixtures::searchUserFixtureByProperty('isAdmin', true);
        $this->adminUser = $this->userRepository->findOneBy(['login' => $userFixtureDto->login]);

    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }

    public function getSourceDataAccessDeny(): \Generator
    {
        $userFixtureDto = AppFixtures::searchUserFixtureByProperty('isAdmin', false);
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['login' => $userFixtureDto->login]);

        yield 'Access deny list of deleted micro posts by user with role ROLE_USER' => [
            $user,
            self::URL_EN_DELETED_MICROPOST,
        ];

        $uri = sprintf(self::URL_EN_RESTORE_MICROPOST, $user->getPosts()->first()->getUuid());

        yield 'Access deny restore deleted micro post by user with role ROLE_USER' => [
            $user,
            $uri,
        ];
    }

    /**
     * @dataProvider getSourceDataAccessDeny
     */
    public function testListAccessDeny(User $user, string $uri): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $client->loginUser($user);
        $client->request('GET', $uri);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testListSuccess(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $client->loginUser($this->adminUser);
        $crawler = $client->request('GET', self::URL_EN_DELETED_MICROPOST);
        self::assertResponseIsSuccessful();

        $card = $crawler->filter('.card.post-item')->first();
        self::assertNotEmpty($card->html(''));
    }

    public function testRestoreSuccess(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        /** @var MicroPost $microPost */
        $microPost = $this->microPostRepository->findOneBy(['user' => $this->adminUser]);
        $microPost->setDeleteAt(new \DateTime());
        $this->em->persist($microPost);
        $this->em->flush();

        $client->loginUser($this->adminUser);
        $uri = sprintf(self::URL_EN_RESTORE_MICROPOST, $microPost->getUuid());
        $client->request('GET', $uri);
        self::assertResponseRedirects();

        $location = $client->getResponse()->headers->get('location');
        $urlLocation = sprintf(self::URL_EN_VIEW_MICROPOST, $microPost->getUuid());
        self::assertStringStartsWith($urlLocation, $location);

        $crawler = $client->followRedirect();
        $card = $crawler->filter('.card.post-item')->first();
        self::assertNotEmpty($card->html(''));

        self::assertEquals($microPost->getContent(), $card->filter('.card-text')->first()->html(''));
    }

    public function testRestoreFail(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $client->loginUser($this->adminUser);
        $uri = sprintf(self::URL_EN_RESTORE_MICROPOST, UuidV4::v4()->toRfc4122());
        $client->request('GET', $uri);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}