<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ApiResource(
    collectionOperations: [
        "get",
        "post" => ["security" => "is_granted('IS_AUTHENTICATED_FULLY')",
            "normalization_context" => ["groups" => ["get-comment-with-author"]]],
        "api_blog_posts_comments_get_subresource" => ["normalization_context" =>
            ["groups" => ["get-comment-with-author"]]]
    ],
    itemOperations: ["get", "put" => ["security" =>
        "is_granted('IS_AUTHENTICATED_FULLY') and object.getAuthor() == user"]],
    denormalizationContext: ["groups" => ["post"]],
)]
class Comment implements AuthoredEntityInterface, PublishedDateEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["get-comment-with-author"])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Groups(["post", "get-comment-with-author"])]
    private ?string $content = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'comments')]
    #[Groups(["get-comment-with-author"])]
    private ?User $author;

    #[ORM\ManyToOne(targetEntity: BlogPost::class, inversedBy: 'comments')]
    #[Assert\NotBlank]
    #[Groups(["post"])]
    private ?BlogPost $post;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["get-comment-with-author"])]
    private ?\DateTimeInterface $published = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPublished(): ?\DateTimeInterface
    {
        return $this->published;
    }

    public function setPublished(\DateTimeInterface $published): PublishedDateEntityInterface
    {
        $this->published = $published;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?UserInterface $author): AuthoredEntityInterface
    {
        $this->author = $author;
        return $this;
    }

    public function getPost(): ?BlogPost
    {
        return $this->post;
    }

    public function setPost(?BlogPost $post): self
    {
        $this->post = $post;
        return $this;
    }
}
