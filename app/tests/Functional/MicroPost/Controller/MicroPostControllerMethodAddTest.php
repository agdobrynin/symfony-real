<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class MicroPostControllerMethodAddTest extends WebTestCase
{
    protected const URL_POST_ADD = '/micro-post/en/add';
    /**
     * @var UserRepository
     */
    private $userRepository;
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
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->faker = Factory::create();
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

        $user = $this->userRepository->findOneBy(['login' => 'admin']);
        $client->loginUser($user);

        $crawler = $client->request('GET', self::URL_POST_ADD);
        self::assertResponseIsSuccessful();

        $contentEl = $crawler->filter('textarea[name$="[content]"]');
        $contentForPost = $this->faker->realTextBetween(278, 280);
        $form = $contentEl->closest('form')->form([
            $contentEl->attr('name') => $contentForPost,
        ]);

        $client->submit($form);
        self::assertResponseRedirects();

        $redirectTo = $client->getResponse()->headers->get('location');
        self::assertNotEmpty($redirectTo);

        $crawler = $client->followRedirect();

        $contentLastPostOnPage = $crawler->filter('main .card-body .card-text')->first()->text();
        self::assertEquals($contentForPost, $contentLastPostOnPage);

        $this->em->refresh($user);
        $post = $user->getPosts()->last();
        self::assertEquals($contentForPost, $post->getContent());
    }

    public function testNewFormAuthUserWithUnprocessableEntity(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $user = $this->userRepository->findOneBy(['login' => 'admin']);
        $client->loginUser($user);

        $crawler = $client->request('GET', self::URL_POST_ADD);
        $contentEl = $crawler->filter('textarea[name$="[content]"]');
        // Is too long length of content MicroPost
        $contentForPost = $this->faker->text(500);
        $form = $contentEl->closest('form')->form([
            $contentEl->attr('name') => $contentForPost,
        ]);

        $crawler = $client->submit($form);
        $this->unprocessableEntity($client->getResponse()->getStatusCode(), $crawler);

        // Test for empty content
        $form = $contentEl->closest('form')->form([
            $contentEl->attr('name') => '',
        ]);
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