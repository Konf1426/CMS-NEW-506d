<?php declare(strict_types=1);

namespace App\Api\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Content;
use App\Entity\User;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\SecurityBundle\Security;

final class ContentProcessor implements ProcessorInterface
{
    private EntityManagerInterface $entityManager;
    private Security $security;
    /** @var ProcessorInterface<Content, Operation> */
    private ProcessorInterface $persistProcessor;

    /**
     * @param ProcessorInterface<Content, Operation> $persistProcessor
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security,
        ProcessorInterface $persistProcessor,
    ) {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->persistProcessor = $persistProcessor;
    }

    /**
     * @param Content $data
     */
    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): mixed {
        // Récupérer l'utilisateur connecté
        $user = $this->security->getUser();

        // Vérifie que l'utilisateur est connecté et est une instance de User
        if (!$user instanceof User) {
            throw new RuntimeException('Vous devez être authentifié pour créer du contenu.');
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
                ++$i;
            }

            $data->setSlug($slug);
        }

        // Persiste le contenu en appelant le processeur existant
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
