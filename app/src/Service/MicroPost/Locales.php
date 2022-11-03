<?php
declare(strict_types=1);

namespace App\Service\MicroPost;

class Locales implements LocalesInterface
{
    private $appSupportedLocales;

    public function __construct(string $appSupportedLocales)
    {
        $this->appSupportedLocales = explode('|', $appSupportedLocales);

        if (!$this->appSupportedLocales) {
            throw new \LogicException('Can not parsing support locales');
        }
    }

    public function getLocales(): array
    {
        return $this->appSupportedLocales;
    }

    public function getDefaultLocale(): string
    {
        return $this->appSupportedLocales[0];
    }
}
