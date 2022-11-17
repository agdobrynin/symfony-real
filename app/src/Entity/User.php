<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 * @UniqueEntity(
 *     fields={"email"},
 *     message="There is already an account with this email 🔒"
 * )
 * @UniqueEntity(
 *     fields={"login"},
 *     message="Login {{ value }} is already exist."
 * )
 * @UniqueEntity(
 *     fields={"nick"},
 *     message="Nickname {{ value }} is already taken by someone."
 * )
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_DEFAULT = [self::ROLE_USER];

    /**
     * @see User::$following
     */
    public const FIELD_NAME_FOR_NOTIFICATION_FOLLOW = 'following';

    // public const FIELD_NAME_OF

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(type="uuid", unique=true, nullable=false)
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=50, unique=true, nullable=false)
     * @Assert\NotBlank
     * @Assert\Length(min=5, max=50)
     * @Assert\Regex(
     *     "/^([a-z_\-\.]+)$/",
     *     message="Not available symbols in login"
     * )
     */
    private $login;

    /**
     * @ORM\Column(type="simple_array")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @var string
     * @ORM\Column(type="string", unique=true, nullable=false, length=100)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(type="string", unique=true, nullable=false, length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min=5, max=255)
     */
    private $nick;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true, length=1)
     * @Assert\NotBlank(allowNull=true)
     * @Assert\Length(min=1, max=1)
     */
    private $emoji;

    /**
     * @ORM\OneToMany(targetEntity=MicroPost::class, mappedBy="user", fetch="EXTRA_LAZY")
     */
    private $posts;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="following", fetch="EXTRA_LAZY")
     */
    private $followers;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="followers", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(
     *     name="user_following",
     *     joinColumns={
     *          @ORM\JoinColumn(name="user_uuid", referencedColumnName="uuid")
     *     },
     *     inverseJoinColumns={
     *          @ORM\JoinColumn(name="following_user_uuid", referencedColumnName="uuid")
     *     }
     * )
     */
    private $following;

    /**
     * @ORM\ManyToMany(targetEntity=MicroPost::class, mappedBy="likedBy", fetch="EXTRA_LAZY")
     */
    private $postsLiked;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $confirmationToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastLoginTime;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isActive;

    /**
     * @ORM\OneToOne(targetEntity=UserPreferences::class, cascade={"persist"})
     */
    private $preferences;

    public function __construct(?string $uuid = null)
    {
        $this->uuid = $uuid ? Uuid::fromString($uuid) : Uuid::v4();
        $this->posts = new ArrayCollection();
        $this->following = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->postsLiked = new ArrayCollection();
        $this->isActive = false;
    }

    public function getUuid(): UuidV4
    {
        return $this->uuid;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->login;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return array_unique($this->roles ?? []);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername(): string
    {
        return $this->login;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getNick(): string
    {
        return $this->nick;
    }

    public function setNick(string $nick): self
    {
        $this->nick = $nick;

        return $this;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    public function getFollowing(): Collection
    {
        return $this->following;
    }

    public function follow(User $user): void
    {
        if ($this->getFollowing()->contains($user)) {
            return;
        }

        $this->getFollowing()->add($user);
    }

    public function getEmoji(): string
    {
        return $this->emoji ?: '👱‍';
    }

    public function setEmoji(?string $emoji = null): self
    {
        $this->emoji = $emoji;

        return $this;
    }

    public function getPostsLiked(): Collection
    {
        return $this->postsLiked;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getLastLoginTime(): ?\DateTimeInterface
    {
        return $this->lastLoginTime;
    }

    public function setLastLoginTime(?\DateTimeInterface $lastLoginTime): self
    {
        $this->lastLoginTime = $lastLoginTime;

        return $this;
    }

    public function getPreferences(): ?UserPreferences
    {
        return $this->preferences;
    }

    public function setPreferences(?UserPreferences $preferences): self
    {
        $this->preferences = $preferences;

        return $this;
    }
}
