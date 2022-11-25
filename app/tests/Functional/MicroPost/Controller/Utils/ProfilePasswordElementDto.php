<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller\Utils;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class ProfilePasswordElementDto
{
    /** @var Form */
    public $form;
    /** @var Crawler */
    public $currentPassword;
    /** @var Crawler */
    public $newPassword;
    /** @var Crawler */
    public $newPasswordRetype;
}
