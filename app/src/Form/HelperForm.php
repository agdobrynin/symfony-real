<?php
declare(strict_types=1);

namespace App\Form;

use App\Service\MicroPost\LocalesInterface;
use Symfony\Component\Intl\Languages;
use function Symfony\Component\String\u;

class HelperForm
{
    public static function getDataForChoiceType(LocalesInterface $locales): array
    {
        $choicesLocale = [];

        foreach ($locales->getLocales() as $locale) {
            $choicesLocale[(string)u(Languages::getName($locale))->title()] = $locale;
        }

        return $choicesLocale;
    }
}
