<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use App\Entity\Content;
use App\Traits\IdTrait;
use App\Traits\CreatedAtTraits;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
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
