<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 */
class Comment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(type="uuid", unique=true, nullable=false)
     */
    private $uuid;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $createAt;

    /**
     * @ORM\Column(type="string", length="200", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(min={10}, max={200})
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="comments")
     * @ORM\JoinColumn(name="user_uuid", referencedColumnName="uuid", nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=MicroPost::class, inversedBy="comments")
     * @ORM\JoinColumn(name="post_uuid", referencedColumnName="uuid", nullable=false, onDelete="CASCADE")
     */
    private $post;

    public function __construct(?string $uuid = null)
    {
        $this->uuid = $uuid ? Uuid::fromString($uuid) : Uuid::v4();
        $this->createAt = new \DateTime();
    }

    public function getUuid(): UuidV4
    {
        return $this->uuid;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setPost(MicroPost $post): self
    {
        $this->post = $post;

        return $this;
    }

    public function getPost(): MicroPost
    {
        return $this->post;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreateAt(): \DateTimeInterface
    {
        return $this->createAt;
    }
}
