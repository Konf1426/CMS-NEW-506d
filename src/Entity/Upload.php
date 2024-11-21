<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Api\Action\UploadAction;
use App\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['upload:read']]),
        new Post(
            controller: UploadAction::class,
            deserialize: false,
            normalizationContext: ['groups' => ['upload:read']],
            denormalizationContext: ['groups' => ['upload:write']]
        )
    ],
    normalizationContext: ['groups' => ['upload:read']],
    denormalizationContext: ['groups' => ['upload:write']]
)]
class Upload
{
    use IdTrait;

    #[ORM\Column(type: 'string')]
    #[Groups(['upload:read', 'upload:write', 'content:read', 'content:write'])]
    private ?string $path = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['upload:read', 'upload:write', 'content:read', 'content:write'])]
    private ?string $originalName = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['upload:read', 'upload:write', 'content:read', 'content:write'])]
    private ?string $mimeType = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['upload:read', 'content:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['upload:read'])]
    private ?int $size = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(?string $originalName): self
    {
        $this->originalName = $originalName;
        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): self
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;
        return $this;
    }
}
