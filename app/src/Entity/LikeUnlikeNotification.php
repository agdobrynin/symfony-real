<?php

namespace App\Entity;

use App\Repository\LikeUnlikeNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LikeUnlikeNotificationRepository::class)
 */
abstract class LikeUnlikeNotification extends Notification
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MicroPost")
     * @ORM\JoinColumn(name="post_uuid", referencedColumnName="uuid")
     */
    private $post;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="by_user_uuid", referencedColumnName="uuid")
     */
    private $byUser;

    public function getPost(): ?MicroPost
    {
        return $this->post;
    }

    public function setPost(MicroPost $post): self
    {
        $this->post = $post;

        return $this;
    }

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
