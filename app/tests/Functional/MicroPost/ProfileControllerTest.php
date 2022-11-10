<?php
declare(strict_types=1);

namespace App\Tests\Funtional\MicroPost\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileControllerTest extends WebTestCase
{
    public function testProfileViewIsAnonymous(): void
    {
        $client = static::createClient();
        $client->request('GET', '/micro-post/en/profile/view');
        self::assertResponseRedirects();

        $crawlerLoginPage = $client->followRedirect();
        $this->assertCount(1, $crawlerLoginPage->filter('input[name*="_username"]'));
        $this->assertCount(1, $crawlerLoginPage->filter('input[name*="_password"]'));
    }

    // TODO make request as login user to profile view
}
