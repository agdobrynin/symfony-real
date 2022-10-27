<?php

namespace App\Entity;

use App\Repository\UnlikeNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UnlikeNotificationRepository::class)
 */
class UnlikeNotification extends LikeUnlikeNotification
{
}
