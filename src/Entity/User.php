<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(
    collectionOperations:
        ["post" => ["denormalization_context" => ["groups" => ["post"]]]
    ],
    itemOperations: [
        "get" => ["security" => "is_granted('IS_AUTHENTICATED_FULLY')",
            "normalization_context" => ['groups' => ['get']]
        ],
        "put" => ["security" => "is_granted('IS_AUTHENTICATED_FULLY') and object == user",
            "denormalization_context" => ["groups" => ["put"]],
            "normalization_context" => ["groups" => ["get"]]
        ]
    ],
//    denormalizationContext: ['groups' => ['put']],
//    normalizationContext: ['groups' => ['get']],
)]
#[UniqueEntity("email",
    message: "Email already in use")]
#[UniqueEntity("username",
    message: "Username already in use")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    const ROLE_COMMENTATOR = 'ROLE_COMMENTATOR';
    const ROLE_WRITER = 'ROLE_WRITER';
    const ROLE_EDITOR = 'ROLE_EDITOR';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    const DEFAULT_ROLES = [self::ROLE_COMMENTATOR];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["get","get-comment-with-author"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["get", "post","get-comment-with-author", "get-blog-post-with-author"])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    #[Groups(["put", "post"])]
    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: "/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{6,}/",
        message: "Password must contain at least 6 characters with at least one number, one capital letter and one small letter"
    )]
    private ?string $password = null;

    #[Groups(["put", "post"])]
    #[Assert\NotBlank]
    #[Assert\Expression(
        "this.getPassword() === this.getRetypedPassword()",
        message: "Passwords do not match"
    )]
    private ?string $retypedPassword = null;

    #[ORM\Column(length: 255)]
    #[Groups(["get", "post", "put", "get-comment-with-author", "get-blog-post-with-author"])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(["post", "put", "get-admin", "get-owner"])]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(min: 5, max: 255)]
    private ?string $email = null;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: BlogPost::class)]
    #[Groups(["get"])]
    private Collection $posts;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: BlogPost::class)]
    #[Groups(["get"])]
    private Collection $comments;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, length: 200)]
    #[Groups(["get-admin", "get-owner"])]
    private array $roles;

    #[Pure] public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->roles = self::DEFAULT_ROLES;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRetypedPassword(): ?string
    {
        return $this->retypedPassword;
    }

    public function setRetypedPassword(?string $retypedPassword): self
    {
        $this->retypedPassword = $retypedPassword;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    public function eraseCredentials()
    {
        return null;
    }

    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }
}
