<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\Service\MicroPost\LocalesInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testRedirectToDefaultLocale(): void
    {
        $defaultLocale = self::getContainer()->get(LocalesInterface::class)->getDefaultLocale();
        $urlWithDefaultLocale = sprintf('/micro-post/%s/', $defaultLocale);

        self::ensureKernelShutdown();
        $client = self::createClient();
        $client->request('GET', '/micro-post');
        self::assertResponseRedirects();
        $crawler = $client->followRedirect();
        self::assertStringEndsWith($urlWithDefaultLocale, $crawler->getUri());
    }
}
