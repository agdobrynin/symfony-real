<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BloggerControllerTest extends WebTestCase
{
    protected const URL_BLOGGERS_VIEW = '/micro-post/en/bloggers';

    public function testBloggerPage(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $crawler = $client->request('GET', self::URL_BLOGGERS_VIEW);
        self::assertResponseIsSuccessful();

        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
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
