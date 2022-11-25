<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller\Utils;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use function Symfony\Component\String\u;

trait MicroPostFormTrait
{
    public static function getFormWithData(Crawler $crawler, string $content): ?Form
    {
        try {
            // css selector for form MicroPost - add, edit.
            $form = $crawler->filter('form button[name$="[save]"]')->form();

            foreach ($form->all() as $item) {
                if (u($item->getName())->endsWith('[content]')) {
                    $item->setValue($content);
                }
            }

            return $form;
        } catch (\InvalidArgumentException $exception) {
            return null;
        }
    }
}
