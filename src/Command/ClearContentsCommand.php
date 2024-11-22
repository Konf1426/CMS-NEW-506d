<?php declare(strict_types=1);

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:clear-contents',
    description: 'Supprime tous les contenus et commentaires de la base de données',
)]
class ClearContentsCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Suppression des contenus et des commentaires...');

        // Supprime les commentaires en premier (relation avec Content)
        $this->entityManager->createQuery('DELETE FROM App\Entity\Comment')->execute();

        // Supprime les contenus
        $this->entityManager->createQuery('DELETE FROM App\Entity\Content')->execute();

        $output->writeln('Tous les contenus et commentaires ont été supprimés.');

        return Command::SUCCESS;
    }
}
