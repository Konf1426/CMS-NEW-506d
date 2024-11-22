<?php declare(strict_types=1);

namespace App\Api\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Comment;
use App\Entity\Content;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\SecurityBundle\Security;

final class CommentProcessor implements ProcessorInterface
{
    private EntityManagerInterface $entityManager;
    private Security $security;
    private ProcessorInterface $persistProcessor;

    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security,
        ProcessorInterface $persistProcessor,
    ) {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->persistProcessor = $persistProcessor;
    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): mixed {
        // Vérifie que l'objet est une instance de Comment
        if (!$data instanceof Comment) {
            return $data;
        }

        // Récupère l'utilisateur connecté
        $user = $this->security->getUser();

        // Vérifie que l'utilisateur est connecté
        if (!$user instanceof User) {
            throw new RuntimeException('Vous devez être authentifié pour créer un commentaire.');
        }

        // Associe l'utilisateur comme auteur du commentaire
        $data->setAuthor($user);

        // Vérifie que le commentaire est associé à un contenu
        $content = $data->getContentEntity();
        if (!$content) {
            throw new RuntimeException('Le commentaire doit être associé à un contenu.');
        }

        // Vérifie que le contenu existe dans la base
        $contentEntity = $this->entityManager->getRepository(Content::class)->find($content->getId());
        if (!$contentEntity) {
            throw new RuntimeException('Le contenu associé au commentaire n\'existe pas.');
        }

        // Associe le contenu valide au commentaire
        $data->setContentEntity($contentEntity);

        // Utilise le processeur de persistance pour sauvegarder le commentaire
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
