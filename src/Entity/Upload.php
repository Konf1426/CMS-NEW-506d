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

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['upload:read', 'content:read'])]
    private \DateTimeImmutable $createdAt;

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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
