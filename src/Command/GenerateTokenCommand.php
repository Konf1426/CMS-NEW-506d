<?php declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateTokenCommand extends Command
{
    protected static ?string $defaultName = 'app:generate-token';

    private EntityManagerInterface $entityManager;
    private JWTTokenManagerInterface $jwtManager;

    public function __construct(EntityManagerInterface $entityManager, JWTTokenManagerInterface $jwtManager)
    {
        $this->entityManager = $entityManager;
        $this->jwtManager = $jwtManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Génère un token JWT pour un utilisateur.')
            ->addArgument('email', InputArgument::REQUIRED, 'Adresse email de l\'utilisateur');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');

        // Récupérer l'utilisateur par email
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $output->writeln('<error>Aucun utilisateur trouvé avec cet email.</error>');

            return Command::FAILURE;
        }

        // Générer le token JWT
        $token = $this->jwtManager->create($user);

        $output->writeln('<info>Token JWT généré avec succès :</info>');
        $output->writeln($token);

        return Command::SUCCESS;
    }
}
