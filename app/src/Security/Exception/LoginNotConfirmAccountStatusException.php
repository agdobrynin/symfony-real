<?php
declare(strict_types=1);

namespace App\Security\Exception;

class LoginNotConfirmAccountStatusException extends \Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException
{
}
