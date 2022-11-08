<?php
declare(strict_types=1);

namespace App\Tests\Integration\Service\MicroPost;

use App\Service\MicroPost\Locales;
use App\Service\MicroPost\LocalesInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LocalesTest extends KernelTestCase
{
    public function testLocales(): void
    {
        $locales = static::getContainer()->get(LocalesInterface::class);
        $this->assertInstanceOf(LocalesInterface::class, $locales);

        $configLocales = static::getContainer()->getParameter('app.supported_locales');
        $this->assertEquals($configLocales, implode('|', $locales->getLocales()));
        $this->assertStringStartsWith($locales->getDefaultLocale() . '|', $configLocales);
    }

    public function testBadContructParam(): void
    {
        self::expectException(\LogicException::class);
        new Locales('');
    }
}
