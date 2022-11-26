<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller\Utils;

class Helper
{
    public static function randString(int $length): string
    {
        return \bin2hex(\random_bytes($length / 2));
    }
}
