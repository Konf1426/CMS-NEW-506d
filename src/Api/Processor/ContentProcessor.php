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
        // Vérifie si l'objet est bien une instance de Content
        if (!$data instanceof Content) {
            return $data;
        }

        // Associer l'utilisateur connecté comme auteur
        $user = $this->security->getUser();
        if (!$user) {
            throw new \RuntimeException('L’utilisateur doit être authentifié pour créer un contenu.');
        }
        $data->setAuthor($user);

        // Générer le slug si non défini
        if (empty($data->getSlug())) {
            $slugify = new Slugify();
            $baseSlug = $slugify->slugify($data->getTitle());
            $slug = $baseSlug;

            // Vérifier l'unicité du slug
            $i = 1;
            while ($this->entityManager->getRepository(Content::class)->findOneBy(['slug' => $slug])) {
                $slug = $baseSlug . '-' . $i;
                $i++;
            }

            $data->setSlug($slug);
        }

        // Persister les données en appelant le processeur existant
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
