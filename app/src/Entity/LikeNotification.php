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
    private $posts;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="user_uuid", referencedColumnName="uuid")
     */
    private $likedBy;

    /**
     * @return mixed
     */
    public function getPosts()
    {
        return $this->posts;
    }

    public function setPosts(MicroPost $posts): void
    {
        $this->posts = $posts;
    }

    /**
     * @return mixed
     */
    public function getLikedBy()
    {
        return $this->likedBy;
    }

    /**
     * @param mixed $likedBy
     */
    public function setLikedBy($likedBy): void
    {
        $this->likedBy = $likedBy;
    }
}
