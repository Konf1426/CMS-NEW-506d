<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use App\Api\Processor\CreateUserProcessor;
use App\Api\Resource\CreateUser;
use App\Traits\CreatedAtTraits;
use App\Traits\IdTrait;
use App\Validator\UnregistredEmail;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(
    security: "is_granted('ROLE_ADMIN') or object == user",
    operations: [
        new \ApiPlatform\Metadata\GetCollection(),
        new \ApiPlatform\Metadata\Get(),
        new \ApiPlatform\Metadata\Post(security: "is_granted('ROLE_ADMIN')"),
        new \ApiPlatform\Metadata\Patch(security: "is_granted('ROLE_ADMIN') or object == user"),
        new \ApiPlatform\Metadata\Delete(security: "is_granted('ROLE_ADMIN')"),

        new \ApiPlatform\Metadata\Patch(security: "object == user or is_granted('ROLE_ADMIN')"),

        new Post(
            input: CreateUser::class,
            processor: CreateUserProcessor::class
        )
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
    #[Assert\Email(message: "Veuillez saisir un email valide.")]
    #[UnregistredEmail(message: "L'email {{ string }} est déjà utilisé.")]
    private ?string $email = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isActive = false;

    /**
     * @var Collection<int, Content>
     */
    #[ORM\OneToMany(targetEntity: Content::class, mappedBy: 'author', cascade: ['persist', 'remove'])]
    private Collection $contents;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'author', cascade: ['persist', 'remove'])]
    private Collection $comments;

    public function __construct()
    {
        $this->setCreatedAt();
        $this->setId();
        $this->contents = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    // Identifiant unique pour l'utilisateur
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    // Récupérer les rôles
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER'; // Tous les utilisateurs ont au moins ce rôle

        return array_unique($roles);
    }

    // Définir les rôles
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    // Vérifier si l'utilisateur est administrateur
    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->roles, true);
    }

    // Vérifier si l'utilisateur est abonné
    public function isSubscriber(): bool
    {
        return in_array('ROLE_SUBSCRIBER', $this->roles, true);
    }

    // Vérifier si l'utilisateur est actif
    public function isActive(): bool
    {
        return $this->isActive;
    }

    // Activer l'utilisateur
    public function activate(): self
    {
        $this->isActive = true;

        return $this;
    }

    // Désactiver l'utilisateur
    public function deactivate(): self
    {
        $this->isActive = false;

        return $this;
    }

    // Récupérer le mot de passe
    public function getPassword(): ?string
    {
        return $this->password;
    }

    // Définir le mot de passe
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    // Récupérer l'email
    public function getEmail(): ?string
    {
        return $this->email;
    }

    // Définir l'email
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    // Récupérer le prénom
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    // Définir le prénom
    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    // Récupérer le nom de famille
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    // Définir le nom de famille
    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    // Récupérer les contenus associés
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

    // Récupérer les commentaires associés
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
