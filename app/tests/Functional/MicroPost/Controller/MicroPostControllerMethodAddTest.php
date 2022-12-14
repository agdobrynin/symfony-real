<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\User;
use App\Tests\Functional\MicroPost\Controller\Utils\MicroPostFormTrait;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Response;

class MicroPostControllerMethodAddTest extends WebTestCase
{
    use MicroPostFormTrait;

    protected const URL_POST_ADD = '/micro-post/en/add';
    /**
     * @var \App\Repository\UserRepository
     */
    private $userRepository;
    /**
     * @var \Doctrine\Persistence\ObjectManager
     */
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
        $this->faker = Factory::create();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }

    public function testOpenNewFormNotAuthUser(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('GET', self::URL_POST_ADD);
        self::assertResponseRedirects();
        $crawler = $client->followRedirect();
        self::assertStringEndsWith('/micro-post/en/login', $crawler->getUri());
    }

    public function testNewFormAuthUser(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $user = $this->userRepository->findOneBy([]);
        $client->loginUser($user);

        $crawler = $client->request('GET', self::URL_POST_ADD);
        self::assertResponseIsSuccessful();

        $contentForPost = $this->faker->realTextBetween(278, 280);
        $form = self::getFormWithData($crawler, $contentForPost);
        self::assertInstanceOf(Form::class, $form);

        $client->submit($form);
        self::assertResponseRedirects();

        $redirectTo = $client->getResponse()->headers->get('location');
        self::assertNotEmpty($redirectTo);

        $crawler = $client->followRedirect();

        $contentLastPostOnPage = $crawler->filter('main .card-body .card-text')->first()->text();
        self::assertEquals($contentForPost, $contentLastPostOnPage);

        $this->em->refresh($user);
        self::assertEquals($contentForPost, $user->getPosts()->last()->getContent());
    }

    public function testNewFormAuthUserWithUnprocessableEntity(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $dto = AppFixtures::searchUserFixtureByProperty('isAdmin', true);
        $user = $this->userRepository->findOneBy(['login' => $dto->login]);
        $client->loginUser($user);

        $crawler = $client->request('GET', self::URL_POST_ADD);
        self::assertResponseIsSuccessful();

        // test too large content
        $contentForPost = $this->faker->text(500);
        $form = self::getFormWithData($crawler, $contentForPost);
        self::assertInstanceOf(Form::class, $form);
        $crawler = $client->submit($form);
        $this->unprocessableEntity($client->getResponse()->getStatusCode(), $crawler);

        // Test for empty content
        $form = self::getFormWithData($crawler, '');
        $crawler = $client->submit($form);
        $this->unprocessableEntity($client->getResponse()->getStatusCode(), $crawler);
    }

    protected function unprocessableEntity(int $responseCode, Crawler $crawler): void
    {
        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $responseCode);

        $contentEl = $crawler->filter('textarea.is-invalid[name$="[content]"]');
        self::assertCount(1, $contentEl);

        $hintEl = $contentEl->nextAll()->filter('div.invalid-feedback');
        self::assertCount(1, $hintEl);
        self::assertNotEmpty($hintEl->text());
    }
}
