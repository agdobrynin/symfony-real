<?php
declare(strict_types=1);

namespace App\Form;

use App\Service\MicroPost\LocalesInterface;
use Symfony\Component\Intl\Languages;
use function Symfony\Component\String\u;

class HelperForm
{
    public static function getDataForChoiceType(LocalesInterface $locales, string $displayLocale = null): array
    {
        $choicesLocale = [];

        foreach ($locales->getLocales() as $locale) {
            $choicesLocale[(string)u(Languages::getName($locale, $displayLocale))->title()] = $locale;
        }

        return $choicesLocale;
    }
}
