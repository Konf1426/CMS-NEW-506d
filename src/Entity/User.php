<?php declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Api\Processor\CreateUserProcessor;
use App\Api\Resource\CreateUser;
use App\Repository\UserRepository;
use App\Traits\CreatedAtTraits;
use App\Traits\IdTrait;
use App\Validator\UnregistredEmail;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use function in_array;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(
    security: "is_granted('ROLE_ADMIN') or object == user",
    operations: [
        new \ApiPlatform\Metadata\GetCollection(),
        new \ApiPlatform\Metadata\Get(),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new \ApiPlatform\Metadata\Patch(security: "is_granted('ROLE_ADMIN') or object == user"),
        new \ApiPlatform\Metadata\Delete(security: "is_granted('ROLE_ADMIN')"),
        new Post(
            input: CreateUser::class,
            processor: CreateUserProcessor::class
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: ['email' => 'partial'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use CreatedAtTraits;
    use IdTrait;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "L'email ne peut pas être vide.")]
    #[Assert\Email(message: 'Veuillez saisir un email valide.')]
    #[UnregistredEmail(message: "L'email {{ string }} est déjà utilisé.")]
    private ?string $email = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $lastName = null;

    /** @var array<string> */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isActive = false;

    /** @var Collection<int, Content> */
    #[ORM\OneToMany(targetEntity: Content::class, mappedBy: 'author', cascade: ['persist', 'remove'])]
    private Collection $contents;

    /** @var Collection<int, Comment> */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'author', cascade: ['persist', 'remove'])]
    private Collection $comments;

    public function __construct()
    {
        $this->setCreatedAt();
        $this->setId();
        $this->contents = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /** @return array<string> */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /** @param array<string> $roles */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->roles, true);
    }

    public function isSubscriber(): bool
    {
        return in_array('ROLE_SUBSCRIBER', $this->roles, true);
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function activate(): self
    {
        $this->isActive = true;

        return $this;
    }

    public function deactivate(): self
    {
        $this->isActive = false;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /** @return Collection<int, Content> */
    public function getContents(): Collection
    {
        return $this->contents;
    }

    public function addContent(Content $content): self
    {
        if (!$this->contents->contains($content)) {
            $this->contents->add($content);
            $content->setAuthor($this);
        }

        return $this;
    }

    public function removeContent(Content $content): self
    {
        if ($this->contents->removeElement($content)) {
            if ($content->getAuthor() === $this) {
                $content->setAuthor(null);
            }
        }

        return $this;
    }

    /** @return Collection<int, Comment> */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setAuthor($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }
}
