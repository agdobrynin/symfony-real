<?php

namespace App\Entity;

use App\Repository\LikeNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LikeNotificationRepository::class)
 */
class LikeNotification extends LikeUnlikeNotification
{
}
