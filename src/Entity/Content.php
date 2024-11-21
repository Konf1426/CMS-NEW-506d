<?php

namespace App\Entity;

use App\Repository\ContentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use App\Api\Filter\UuidFilter;
use ApiPlatform\Metadata\ApiFilter;
use App\Api\Processor\ContentProcessor;
use App\Traits\IdTrait;
use App\Traits\CreatedAtTraits;
use App\Api\Action\ImportContentAction;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ContentRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['content:read']],
    denormalizationContext: ['groups' => ['content:write']],
    operations: [
        new \ApiPlatform\Metadata\GetCollection(),
        new \ApiPlatform\Metadata\Get(),
        new Post(processor: ContentProcessor::class),
        new Patch(processor: ContentProcessor::class),
    ]
)]
#[ApiResource(
    operations: [
        new \ApiPlatform\Metadata\GetCollection(),
        new \ApiPlatform\Metadata\Get(),
        new \ApiPlatform\Metadata\Post(),
        new \ApiPlatform\Metadata\Post(
            uriTemplate: '/contents/import',
            controller: ImportContentAction::class,
            deserialize: false,
            name: 'import_contents'
        )
    ],
    normalizationContext: ['groups' => ['content:read']],
    denormalizationContext: ['groups' => ['content:write']]
)]

#[ApiFilter(SearchFilter::class, properties: [
    'title' => 'partial', // Recherche partielle sur le titre
    'slug' => 'exact',    // Recherche exacte sur le slug
    'tags' => 'partial',  // Recherche partielle sur les tags
])]
#[ApiFilter(UuidFilter::class, properties: ['author'])] // Filtrer par UUID
#[ApiFilter(BooleanFilter::class, properties: ['image'])] // Filtrer si une image existe
#[ApiFilter(DateFilter::class, properties: ['createdAt'])] // Filtrer par date de création
class Content
{
    use CreatedAtTraits;
    use IdTrait;

    #[ORM\Column(length: 255)]
    #[Groups(['content:read', 'content:write'])]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['content:read', 'content:write'])]
    private ?string $metaTitle = null;

    #[ORM\Column(length: 255)]
    #[Groups(['content:read', 'content:write'])]
    private ?string $metaDescription = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['content:read', 'content:write'])]
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    #[Groups(['content:read', 'content:write'])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    #[Groups(['content:read', 'content:write'])]
    private ?array $tags = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'contents')]
    #[ORM\JoinColumn(name: 'author_uuid', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Groups(['content:read', 'content:write'])]
    private ?User $author = null;

    #[ORM\ManyToOne(targetEntity: Upload::class)]
    #[ORM\JoinColumn(name: 'image_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[Groups(['content:read', 'content:write'])]
    private ?Upload $image = null;


    public function __construct()
    {
        $this->setCreatedAt();
        $this->setId();
    }

    #[ORM\PrePersist]
    public function initializeCreatedAt(): void
    {
        $this->setCreatedAt();
    }

    #[ORM\OneToMany(mappedBy: 'contentEntity', targetEntity: Comment::class, cascade: ['remove'])]
    #[Groups(['content:read'])]
    private iterable $comments;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(string $metaTitle): self
    {
        $this->metaTitle = $metaTitle;
        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): self
    {
        $this->tags = $tags;
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

    public function getImage(): ?Upload
    {
        return $this->image;
    }

    public function setImage(?Upload $image): self
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @return iterable<Comment>
     */
    public function getComments(): iterable
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setContentEntity($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            if ($comment->getContentEntity() === $this) {
                $comment->setContentEntity(null);
            }
        }

        return $this;
    }
}
