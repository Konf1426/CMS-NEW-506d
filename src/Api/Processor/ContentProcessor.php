<?php

namespace App\Api\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Content;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Cocur\Slugify\Slugify;

final class ContentProcessor implements ProcessorInterface
{
    private EntityManagerInterface $entityManager;
    private Security $security;
    private ProcessorInterface $persistProcessor;

    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security,
        ProcessorInterface $persistProcessor
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
        // Vérifie que l'objet est une instance de Content
        if (!$data instanceof Content) {
            return $data;
        }

        // Récupérer l'utilisateur connecté
        $user = $this->security->getUser();

        // Vérifie que l'utilisateur est connecté
        if (!$user) {
            throw new \RuntimeException('Vous devez être authentifié pour créer du contenu.');
        }

        // Vérifie que l'utilisateur a le rôle ROLE_ADMIN
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            throw new \RuntimeException('Seuls les administrateurs peuvent créer du contenu.');
        }

        // Associe l'utilisateur comme auteur du contenu
        $data->setAuthor($user);

        // Génère un slug si aucun n'est défini
        if (empty($data->getSlug())) {
            $slugify = new Slugify();
            $baseSlug = $slugify->slugify($data->getTitle());
            $slug = $baseSlug;

            // Vérifie l'unicité du slug
            $i = 1;
            while ($this->entityManager->getRepository(Content::class)->findOneBy(['slug' => $slug])) {
                $slug = $baseSlug . '-' . $i;
                $i++;
            }

            $data->setSlug($slug);
        }

        // Persiste le contenu en appelant le processeur existant
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
