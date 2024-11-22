<?php declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Api\Action\ImportContentAction;
use App\Api\Processor\ContentProcessor;
use App\Repository\ContentRepository;
use App\Traits\CreatedAtTraits;
use App\Traits\IdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
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
        new Post(
            uriTemplate: '/contents/import',
            controller: ImportContentAction::class,
            deserialize: false,
            name: 'import_contents'
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'title' => 'partial',
    'slug' => 'exact',
    'tags' => 'partial',
])]
#[ApiFilter(BooleanFilter::class, properties: ['image'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
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

    /** @var array<string> */
    #[ORM\Column(type: 'array', nullable: false)]
    #[Groups(['content:read', 'content:write'])]
    private array $tags = [];

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'contents')]
    #[ORM\JoinColumn(name: 'author_uuid', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Groups(['content:read', 'content:write'])]
    private ?User $author = null;

    #[ORM\ManyToOne(targetEntity: Upload::class)]
    #[ORM\JoinColumn(name: 'image_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[Groups(['content:read', 'content:write'])]
    private ?Upload $image = null;

    /** @var Collection<int, Comment> */
    #[ORM\OneToMany(mappedBy: 'contentEntity', targetEntity: Comment::class, cascade: ['remove'])]
    #[Groups(['content:read'])]
    private Collection $comments;

    public function __construct()
    {
        $this->setCreatedAt();
        $this->setId();
        $this->comments = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function initializeCreatedAt(): void
    {
        $this->setCreatedAt();
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

    /**
     * @return array<string>
     */
    public function getTags(): array
    {
        return $this->tags ?? [];
    }

    /**
     * @param array<string> $tags
     */
    public function setTags(array $tags): self
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
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setContentEntity($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getContentEntity() === $this) {
                $comment->setContentEntity(null);
            }
        }

        return $this;
    }
}
