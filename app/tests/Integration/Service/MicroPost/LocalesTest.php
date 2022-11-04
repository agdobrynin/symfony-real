<?php
declare(strict_types=1);

namespace App\Tests\Integration\Service\MicroPost;

use App\Service\MicroPost\Locales;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LocalesTest extends KernelTestCase
{
    public function testLocales(): void
    {
        $kernel = self::bootKernel();
        $this->assertSame('test', $kernel->getEnvironment());

        $locales = static::getContainer()->get(Locales::class);
        $this->assertInstanceOf(Locales::class, $locales);

        $configLocales = static::getContainer()->getParameter('app.supported_locales');
        $this->assertEquals($configLocales, implode('|', $locales->getLocales()));
        $this->assertStringStartsWith($locales->getDefaultLocale() . '|', $configLocales);
    }
}
