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

use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ContentRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new \ApiPlatform\Metadata\GetCollection(),
        new \ApiPlatform\Metadata\Get(),
        new Post(processor: ContentProcessor::class),
        new Patch(processor: ContentProcessor::class),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'title' => 'partial', // Recherche partielle sur le titre
    'slug' => 'exact',    // Recherche exacte sur le slug
    'tags' => 'partial',  // Recherche partielle sur les tags
])]
#[ApiFilter(UuidFilter::class, properties: ['author'])] // Filtrer par UUID
#[ApiFilter(BooleanFilter::class, properties: ['coverImage'])] // Filtrer si une image de couverture existe
#[ApiFilter(DateFilter::class, properties: ['createdAt'])] // Filtrer par date de création
class Content
{
    use CreatedAtTraits;
    use IdTrait;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coverImage = null;

    #[ORM\Column(length: 255)]
    private ?string $metaTitle = null;

    #[ORM\Column(length: 255)]
    private ?string $metaDescription = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $tags = null;

    #[ORM\ManyToOne(inversedBy: 'contents', targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'author_uuid', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?User $author = null;

    #[ORM\PrePersist]
    public function initializeCreatedAt(): void
    {
        $this->setCreatedAt();
    }

    #[ORM\OneToMany(mappedBy: 'contentEntity', targetEntity: Comment::class, cascade: ['remove'])]
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

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(?string $coverImage): self
    {
        $this->coverImage = $coverImage;
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
