<?php

namespace App\Service\MicroPost;

interface LocalesInterface
{
    /**
     * @return string[]
     */
    public function getLocales(): array;

    public function getDefaultLocale(): string;
}
