<?php
declare(strict_types=1);

namespace App\Dto;

class SimplePostDto
{
    /**
     * @var string|null
     */
    public $uuid;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $content;

    /**
     * @var \DateTimeInterface
     */
    public $date;

    public function __construct()
    {
        $this->date = new \DateTime();
    }
}
