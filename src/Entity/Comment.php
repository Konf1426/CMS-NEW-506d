<?php declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Api\Processor\CommentProcessor;
use App\Repository\CommentRepository;
use App\Traits\CreatedAtTraits;
use App\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ApiResource(
    operations: [
        new \ApiPlatform\Metadata\Post(processor: CommentProcessor::class),
        new \ApiPlatform\Metadata\GetCollection(),
        new \ApiPlatform\Metadata\Get(),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: ['contentEntity.id' => 'exact'])]
class Comment
{
    use CreatedAtTraits;
    use IdTrait;

    #[ORM\Column(type: 'text')]
    private ?string $content = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\ManyToOne(targetEntity: Content::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Content $contentEntity = null;

    public function __construct()
    {
        $this->setCreatedAt();
        $this->setId();
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

    public function getContentEntity(): ?Content
    {
        return $this->contentEntity;
    }

    public function setContentEntity(?Content $contentEntity): self
    {
        $this->contentEntity = $contentEntity;

        return $this;
    }
}
