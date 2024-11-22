<?php declare(strict_types=1);

namespace App\Api\Action;

use App\Api\Processor\ContentProcessor;
use App\Entity\Content;
use App\Entity\Upload;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use function in_array;
use const FILTER_VALIDATE_URL;
use const PATHINFO_EXTENSION;

#[AsController]
class ImportContentAction
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ContentProcessor $contentProcessor,
        #[Autowire(param: 'kernel.project_dir')]
        private string $projectDir,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $file = $request->files->get('file');

        // Vérification du fichier CSV
        if (!$file instanceof UploadedFile || 'csv' !== $file->getClientOriginalExtension()) {
            throw new BadRequestHttpException('Veuillez fournir un fichier CSV valide.');
        }

        $filePath = $file->getPathname();
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new RuntimeException('Impossible d\'ouvrir le fichier CSV.');
        }

        $headers = fgetcsv($handle, 0, ',');
        if (!$headers || !in_array('title', $headers) || !in_array('cover', $headers)) {
            throw new RuntimeException('Le fichier CSV ne contient pas les colonnes requises.');
        }

        $contents = [];

        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            $content = new Content();

            // Mapping des colonnes du CSV
            $content->setTitle($data[array_search('title', $headers)] ?? 'Titre par défaut');
            $content->setMetaTitle($data[array_search('meta_title', $headers)] ?? 'Titre Meta par défaut');
            $content->setMetaDescription($data[array_search('meta_description', $headers)] ?? 'Description Meta par défaut');
            $content->setContent($data[array_search('content', $headers)] ?? 'Contenu par défaut');

            // Gestion des tags séparés par "|"
            $tags = $data[array_search('tags', $headers)] ?? '';
            $content->setTags(explode('|', $tags));

            // Gestion de l'image via la colonne "cover"
            $cover = $data[array_search('cover', $headers)] ?? null;
            if ($cover) {
                try {
                    $newImagePath = $this->storeImage($cover);
                    $upload = new Upload();
                    $upload->setId();
                    $upload->setPath($newImagePath);
                    $this->entityManager->persist($upload);
                    $content->setImage($upload);
                } catch (RuntimeException $e) {
                    error_log('Erreur lors du téléchargement de l\'image : ' . $e->getMessage());
                    continue; // Ignore cette ligne et passe à la suivante
                }
            }

            // Appelle le ContentProcessor pour les traitements supplémentaires (slug, etc.)
            $this->contentProcessor->process($content, new \ApiPlatform\Metadata\Post());

            $this->entityManager->persist($content);
            $this->entityManager->flush(); // Sauvegarde en base immédiatement

            // Ajoute les données complètes dans le tableau de réponse
            $contents[] = [
                'title' => $content->getTitle(),
                'slug' => $content->getSlug(),
                'metaTitle' => $content->getMetaTitle(),
                'metaDescription' => $content->getMetaDescription(),
                'tags' => $content->getTags(),
                'image' => $content->getImage()?->getPath(),
            ];
        }

        fclose($handle);

        return new JsonResponse([
            'message' => 'Importation réussie.',
            'importedContents' => $contents,
        ]);
    }

    /**
     * Télécharge une image et la stocke dans le dossier public/medias.
     */
    private function storeImage(string $imageUrl): string
    {
        // Vérifie que l'URL est valide
        if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('URL invalide pour l\'image : ' . $imageUrl);
        }

        // Récupère le contenu de l'image
        $imageContent = @file_get_contents($imageUrl);
        if (!$imageContent) {
            throw new RuntimeException('Impossible de télécharger l\'image : ' . $imageUrl);
        }

        // Récupère ou déduit l'extension du fichier
        $extension = pathinfo($imageUrl, PATHINFO_EXTENSION);
        if (!$extension) {
            $extension = 'jpg'; // Extension par défaut
        }

        $filename = uniqid() . '.' . $extension;
        $destination = $this->projectDir . '/public/medias/' . $filename;

        file_put_contents($destination, $imageContent);

        return '/medias/' . $filename;
    }
}
