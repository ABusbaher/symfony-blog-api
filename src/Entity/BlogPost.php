<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\BlogPostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BlogPostRepository::class)]
#[ApiResource(
    collectionOperations: ['get', 'post' => ["security" => "is_granted('IS_AUTHENTICATED_FULLY')"]],
    itemOperations: ['get', 'put' => ["security" =>
        "is_granted('IS_AUTHENTICATED_FULLY') and object.getAuthor() == user"]]
)]
class BlogPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank]
    #[Assert\Type("\DateTimeInterface")]
    private ?\DateTimeInterface $published = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 5)]
    private ?string $content = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'posts')]
    private ?User $author;

    #[ORM\OneToMany(mappedBy: 'post', targetEntity: Comment::class)]
    private Collection $comments;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank]
    private ?string $slug;

    #[Pure] public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPublished(): ?\DateTimeInterface
    {
        return $this->published;
    }

    public function setPublished(\DateTimeInterface $published): self
    {
        $this->published = $published;

        return $this;
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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }
}
