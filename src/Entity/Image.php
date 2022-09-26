<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\UploadImageAction;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[Vich\Uploadable]
#[ApiResource(
    collectionOperations: [
        "get",
        "post" => [
            "method" => "POST",
            "path" => "/images",
            "controller" => UploadImageAction::class,
            "defaults" => [
                "_api_receive" => false,
            ]
        ]
    ],
    attributes: [
        "order" => [
            "id" => "desc",
        ]
    ],
)]
class Image
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[Vich\UploadableField(mapping: 'images', fileNameProperty: 'url')]
    #[Assert\NotNull]
    private ?File $file = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(["get-blog-post-with-author"])]
    private ?string $url = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): void
    {
        $this->file = $file;
    }

    public function getUrl(): ?string
    {
        return '/images/' . $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }
}