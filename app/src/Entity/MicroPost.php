<?php

namespace App\Entity;

use App\Repository\MicroPostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=MicroPostRepository::class)
 */
class MicroPost
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(type="uuid", unique=true, nullable=false)
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=280, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min=10, minMessage="Is too short. Minumum mustbe {{ limit }} character",
     *     max=280, maxMessage="Is to long. Maximum maybe {{ limit }} character")
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="posts")
     * @ORM\JoinColumn(name="user_uuid", referencedColumnName="uuid")
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    public function __construct(?string $uuid = null)
    {
        $this->uuid = $uuid ? Uuid::fromString($uuid) : Uuid::v4();
    }

    public function getUuid(): UuidV4
    {
        return $this->uuid;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user = null): self
    {
        $this->user = $user;

        return $this;
    }
}
