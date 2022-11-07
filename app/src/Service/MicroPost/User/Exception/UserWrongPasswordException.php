<?php
declare(strict_types=1);

namespace App\Service\MicroPost\User\Exception;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserWrongPasswordException extends AccessDeniedException
{
}
