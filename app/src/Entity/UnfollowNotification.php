<?php

namespace App\Entity;

use App\Repository\UnfollowNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @codeCoverageIgnore
 * @ORM\Entity(repositoryClass=UnfollowNotificationRepository::class)
 */
class UnfollowNotification extends ByUserNotification
{
}
