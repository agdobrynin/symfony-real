<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use Symfony\Component\DomCrawler\Crawler;

class ProfileEditElementDto
{
    /** @var Crawler */
    public $emoji;
    /** @var Crawler */
    public $email;
    /** @var Crawler */
    public $userLocale;
    /** @var Crawler */
    public $userLocaleSelected;
}
