<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\MicroPostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=MicroPostRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class MicroPost
{
    /**
     * @see MicroPost::$likedBy;
     */
    public const FIELD_NAME_FOR_NOTIFICATION_LIKED = 'likedBy';
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
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="posts")
     * @ORM\JoinColumn(name="user_uuid", referencedColumnName="uuid", nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="postsLiked")
     * @ORM\JoinTable(name="post_likes",
     *     joinColumns={
     *          @ORM\JoinColumn(name="post_uuid", referencedColumnName="uuid")
     *     },
     *     inverseJoinColumns={
     *          @ORM\JoinColumn(name="user_uuid", referencedColumnName="uuid")
     *     }
     * )
     */
    private $likedBy;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    public function __construct(?string $uuid = null)
    {
        $this->uuid = $uuid ? Uuid::fromString($uuid) : Uuid::v4();
        $this->likedBy = new ArrayCollection();
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

    /**
     * @ORM\PrePersist()
     */
    public function setDateOnPersist(): void
    {
        if (!$this->date instanceof \DateTimeInterface) {
            $this->date = new \DateTime();
        }
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

    public function getLikedBy(): Collection
    {
        return $this->likedBy;
    }

    public function like(User $user): self
    {
        if (!$this->likedBy->contains($user)) {
            $this->likedBy->add($user);
        }

        return $this;
    }
}
