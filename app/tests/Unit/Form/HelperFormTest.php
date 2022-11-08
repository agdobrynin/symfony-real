<?php

namespace App\Tests\Unit\Form;

use App\Form\HelperForm;
use App\Service\MicroPost\Locales;
use PHPUnit\Framework\TestCase;

class HelperFormTest extends TestCase
{
    public function testChoiceLocaleRu(): void
    {
        $locales = new Locales('ru|en');
        $choices = HelperForm::getDataForChoiceType($locales, $locales->getDefaultLocale());

        self::assertEquals(['Русский', 'Английский'], array_keys($choices));
        self::assertEquals(['ru', 'en'], array_values($choices));
    }

    public function testChoiceLocaleEnOnly(): void
    {
        $locales = new Locales('en');
        $choices = HelperForm::getDataForChoiceType($locales, $locales->getDefaultLocale());

        self::assertEquals(['English'], array_keys($choices));
        self::assertEquals(['en'], array_values($choices));
    }
}
