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
     * @ORM\Column(type="json")
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
     * @ORM\OneToMany(targetEntity=MicroPost::class, mappedBy="user")
     */
    private $posts;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="following")
     */
    private $followers;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="followers")
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

    public function __construct(?string $uuid = null)
    {
        $this->uuid = $uuid ? Uuid::fromString($uuid) : Uuid::v4();
        $this->posts = new ArrayCollection();
        $this->following = new ArrayCollection();
        $this->followers = new ArrayCollection();
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
//        if (null !== $emoji) {
//            preg_match("/^(\u00a9|\u00ae|[\u2000-\u3300]|\ud83c[\ud000-\udfff]|\ud83d[\ud000-\udfff]|\ud83e[\ud000-\udfff])$/", $emoji, $r);
//            dd($r);
//        }

        $this->emoji = $emoji;

        return $this;
    }
}
