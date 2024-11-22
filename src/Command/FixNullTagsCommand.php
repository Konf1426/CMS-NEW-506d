<?php declare(strict_types=1);

namespace App\Command;

use App\Entity\Content;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function sprintf;

#[AsCommand(
    name: 'app:fix-null-tags',
    description: 'Corrige les valeurs NULL pour les tags dans les contenus.',
)]
class FixNullTagsCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repository = $this->entityManager->getRepository(Content::class);
        $contents = $repository->findAll();

        foreach ($contents as $content) {
            if (empty($content->getTags())) { // Vérifie si les tags sont vides ou null
                $content->setTags([]); // Remplace les valeurs vides par un tableau
                $output->writeln(sprintf('Fixing tags for content ID: %s', $content->getId()));
            }
        }

        $this->entityManager->flush();

        $output->writeln('All empty or NULL tags have been fixed.');

        return Command::SUCCESS;
    }
}
