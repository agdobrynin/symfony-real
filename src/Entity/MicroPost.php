<?php

namespace App\Entity;

use App\Repository\MicroPostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=MicroPostRepository::class)
 */
class MicroPost
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true, nullable=false)
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=280, nullable=false)
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    public function __construct()
    {
        $this->uuid = Uuid::v4()->toRfc4122();
    }

    public function getUuid(): ?string
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
}
