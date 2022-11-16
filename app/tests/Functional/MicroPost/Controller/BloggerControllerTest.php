<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BloggerControllerTest extends WebTestCase
{
    protected const URL_BLOGGERS_VIEW = '/micro-post/en/bloggers';
    /** @var \Doctrine\Persistence\ObjectManager */
    private $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = self::getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }

    public function testBloggerPage(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $crawler = $client->request('GET', self::URL_BLOGGERS_VIEW);
        self::assertResponseIsSuccessful();

        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->em->getRepository(User::class);
        $bloggers = $userRepository->findAll();

        foreach ($bloggers as $index => $blogger) {
            $card = $crawler->filter('.blogger-item')->eq($index);

            $cardHeader = $card->filter('.card-header')->text();
            self::assertEquals($blogger->getEmoji() . '@' . $blogger->getNick(), $cardHeader);

            $cardPosts = $card->filter('.blogger-posts')->text();
            self::assertStringEndsWith(': ' . $blogger->getPosts()->count(), $cardPosts);

            $cardFollowers = $card->filter('.blogger-followers')->text();
            self::assertStringEndsWith(': ' . $blogger->getFollowers()->count(), $cardFollowers);
        }
    }
}
