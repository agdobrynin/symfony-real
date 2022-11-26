<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller\Utils;

use App\Entity\User;

class UserRandom
{
    public static function minimal(?\Closure $closure = null): User
    {
        $randString = Helper::randString(6);

        $user = (new User())->setLogin('login' . $randString)
            ->setNick('rand_nick ' . $randString)
            ->setEmail($randString . '@domain.com')
            ->setPassword($randString)
            ->setIsActive(true)
            ->setRoles(User::ROLE_DEFAULT)
            ->setPassword($randString);

        return $closure ? $closure($user) : $user;
    }
}
