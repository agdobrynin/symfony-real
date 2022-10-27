<?php

namespace App\Entity;

use App\Repository\LikeUnlikeNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LikeUnlikeNotificationRepository::class)
 */
abstract class LikeUnlikeNotification extends ByUserNotification
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MicroPost")
     * @ORM\JoinColumn(name="post_uuid", referencedColumnName="uuid")
     */
    private $post;

    public function getPost(): ?MicroPost
    {
        return $this->post;
    }

    public function setPost(MicroPost $post): self
    {
        $this->post = $post;

        return $this;
    }
}
