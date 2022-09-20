<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\ResetPasswordAction;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
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
        ],
        "put-reset-password" => ["security" => "is_granted('IS_AUTHENTICATED_FULLY') and object == user",
            "denormalization_context" => ["groups" => ["put-reset-password"]],
            "method" => "PUT",
            "path" => "/users/{id}/reset-password",
            "controller" => ResetPasswordAction::class
        ],
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
    #[Assert\NotBlank(groups: ["post"])]
    #[Assert\Length(min: 3, max: 255, groups: ["post"])]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    #[Groups(["post"])]
    #[Assert\NotBlank(groups: ["post"])]
    #[Assert\Regex(
        pattern: "/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{6,}/",
        message: "Password must contain at least 6 characters with at least one number, one capital letter and one small letter",
        groups: ["post"]
    )]
    private ?string $password = null;

    #[Groups(["post"])]
    #[Assert\NotBlank(groups: ["post"])]
    #[Assert\Expression(
        "this.getPassword() === this.getRetypedPassword()",
        message: "Passwords do not match", groups: ["post"]
    )]
    private ?string $retypedPassword = null;

    #[Groups(["put-reset-password"])]
    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: "/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{6,}/",
        message: "Password must contain at least 6 characters with at least one number, one capital letter and one small letter"
    )]
    private ?string $newPassword;

    #[Groups(["put-reset-password"])]
    #[Assert\NotBlank]
    #[Assert\Expression(
        "this.getNewPassword() === this.getNewRetypedPassword()",
        message: "Passwords do not match"
    )]
    private ?string $newRetypedPassword;

    #[Groups(["put-reset-password"])]
    #[Assert\NotBlank]
    #[UserPassword()]
    private ?string $oldPassword;

    #[ORM\Column(length: 255)]
    #[Groups(["get", "post", "put", "get-comment-with-author", "get-blog-post-with-author"])]
    #[Assert\NotBlank(groups: ["post"])]
    #[Assert\Length(min: 3, max: 255, groups: ["post", "put"])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(["post", "put", "get-admin", "get-owner"])]
    #[Assert\NotBlank(groups: ["post"])]
    #[Assert\Email(groups: ["post", "put"])]
    #[Assert\Length(min: 5, max: 255, groups: ["post", "put"])]
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

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $passwordChangeDate = null;

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

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function getNewRetypedPassword(): ?string
    {
        return $this->newRetypedPassword;
    }

    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    public function setNewPassword(?string $newPassword): self
    {
        $this->newPassword = $newPassword;
        return $this;
    }

    public function setNewRetypedPassword(?string $newRetypedPassword): self
    {
        $this->newRetypedPassword = $newRetypedPassword;
        return $this;
    }


    public function setOldPassword(?string $oldPassword): self
    {
        $this->oldPassword = $oldPassword;
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

    /**
     * @return mixed
     */
    public function getPasswordChangeDate(): int
    {
        return $this->passwordChangeDate;
    }

    public function setPasswordChangeDate($passwordChangeDate): self
    {
        $this->passwordChangeDate = $passwordChangeDate;
        return $this;
    }


}
