<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="dicsr", type="string")
 * @ORM\DiscriminatorMap({
 *     "abstract-by-user" = "ByUserNotification",
 *     "abstract-like-unlike" = "LikeUnlikeNotification",
 *     "like" = "LikeNotification",
 *     "unlike" = "UnlikeNotification",
 *     "follow" = "FollowNotification",
 *     "unfollow" = "UnfollowNotification",
 *     })
 * @ORM\HasLifecycleCallbacks()
 */
abstract class Notification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="user_uuid", referencedColumnName="uuid")
     */
    private $user;

    /**
     * @ORM\Column(type="boolean")
     */
    private $seen;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $createAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updateAt;

    public function __construct()
    {
        $this->seen = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getSeen(): bool
    {
        return $this->seen;
    }

    public function setSeen(bool $seen): self
    {
        $this->seen = $seen;

        return $this;
    }

    public function getCreateAt(): \DateTimeInterface
    {
        return $this->createAt;
    }

    public function getUpdateAt(): \DateTimeInterface
    {
        return $this->updateAt;
    }

    public function setUpdateAt(\DateTimeInterface $dateTime): self
    {
        $this->updateAt = $dateTime;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     */
    public function setDatesOnPersist(): void
    {
        if (!$this->createAt instanceof \DateTimeInterface) {
            $this->createAt = new \DateTime();
        } else {
            $this->updateAt = new \DateTime();
        }
    }
}
