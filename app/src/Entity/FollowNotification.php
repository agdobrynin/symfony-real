<?php

namespace App\Entity;

use App\Repository\FollowNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FollowNotificationRepository::class)
 */
class FollowNotification extends ByUserNotification
{
}
