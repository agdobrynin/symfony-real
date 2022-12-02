<?php

namespace App\Service\MicroPost;

interface SoftDeleteFilterServiceInterface
{
    public function softDeleteOnlyOn(): void;

    public function softDeletedOn(): void;

    public function allOff(): void;
}
