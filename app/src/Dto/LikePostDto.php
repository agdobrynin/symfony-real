<?php
declare(strict_types=1);

namespace App\Dto;

class LikePostDto
{
    /** @var int */
    public $count = 0;

    public function __construct(int $count)
    {
        $this->count = $count;
    }
}
