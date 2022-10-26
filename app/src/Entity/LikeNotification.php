<?php

namespace App\Entity;

use App\Repository\LikeNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LikeNotificationRepository::class)
 */
class LikeNotification extends Notification
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MicroPost")
     * @ORM\JoinColumn(name="post_uuid", referencedColumnName="uuid")
     */
    private $post;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="liked_by_user_uuid", referencedColumnName="uuid")
     */
    private $likedBy;

    public function getPost(): ?MicroPost
    {
        return $this->post;
    }

    public function setPost(MicroPost $post): void
    {
        $this->post = $post;
    }

    public function getLikedBy(): ?User
    {
        return $this->likedBy;
    }

    public function setLikedBy(User $likedBy): void
    {
        $this->likedBy = $likedBy;
    }
}
