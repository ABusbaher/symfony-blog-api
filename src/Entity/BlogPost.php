<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Repository\BlogPostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

#[ORM\Entity(repositoryClass: BlogPostRepository::class)]
#[ApiFilter(SearchFilter::class, properties: [
    "title" => "ipartial", //case sensitive
    "content" => "partial",
    "author" => "exact",
    "author.name"=> "partial"
])]
#[ApiFilter(DateFilter::class, properties: ["published"])]
#[ApiFilter(RangeFilter::class, properties: ["id"])]
#[ApiFilter(OrderFilter::class,
    properties: ["id", "published", "title"],
    arguments: ["orderParameterName" => "order"]
)]
#[ApiFilter(PropertyFilter::class, arguments: [
    "parameterName" => "properties",
    "overrideDefaultProperties" => false,
    "whitelist" => ["id", "author", "slug", "title", "content"]
])]
#[ApiResource(
    collectionOperations: [
        "get" => ["normalization_context" => ["groups" => ["get-blog-post-with-author"]]],
        "post" => ["security" => "is_granted('ROLE_WRITER')"]
    ],
    itemOperations: ["get", "put" => ["security" =>
        "is_granted('ROLE_EDITOR') or (is_granted('ROLE_WRITER') and object.getAuthor() == user)"]],
    denormalizationContext: ["groups" => ["post"]],
)]

class BlogPost implements AuthoredEntityInterface, PublishedDateEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["get-blog-post-with-author"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(["post", "get-blog-post-with-author"])]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["get-blog-post-with-author"])]
    private ?\DateTimeInterface $published = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 5)]
    #[Groups(["post", "get-blog-post-with-author"])]
    private ?string $content = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'posts')]
    #[Groups(["get-blog-post-with-author"])]
    private ?User $author;

    #[ORM\OneToMany(mappedBy: 'post', targetEntity: Comment::class)]
    #[ApiSubresource]
    #[Groups(["get-blog-post-with-author"])]
    private Collection $comments;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank]
    #[Groups(["post", "get-blog-post-with-author"])]
    private ?string $slug;

    #[ORM\ManyToMany(targetEntity: Image::class)]
    #[ApiSubresource]
    #[Groups(["post", "get-blog-post-with-author"])]
    private Collection $images;

    #[Pure] public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->images = new ArrayCollection();
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

    public function setPublished(\DateTimeInterface $published): PublishedDateEntityInterface
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

    public function setAuthor(UserInterface $author): AuthoredEntityInterface
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

    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): void
    {
        if ($this->images->contains($image)) {
            return;
        }
        $this->images->add($image);
    }

    public function removeImage(Image $image): void
    {
        if (!$this->images->contains($image)) {
            return;
        }
        $this->images->removeElement($image);
    }


}
