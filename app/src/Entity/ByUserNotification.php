<?php

namespace App\Entity;

use App\Repository\ByUserNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ByUserNotificationRepository::class)
 */
abstract class ByUserNotification extends Notification
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="by_user_uuid", referencedColumnName="uuid")
     */
    private $byUser;

    public function getByUser(): ?User
    {
        return $this->byUser;
    }

    public function setByUser(User $byUser): self
    {
        $this->byUser = $byUser;

        return $this;
    }
}
