<?php declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use function in_array;

#[AsCommand(
    name: 'app:create-user',
    description: 'Créer un nouvel utilisateur.',
)]
class CreateUserCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l\'utilisateur')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe de l\'utilisateur')
            ->addArgument('firstName', InputArgument::OPTIONAL, 'Prénom de l\'utilisateur', 'John')
            ->addArgument('lastName', InputArgument::OPTIONAL, 'Nom de l\'utilisateur', 'Doe')
            ->addArgument('roles', InputArgument::OPTIONAL, 'Rôles de l\'utilisateur séparés par une virgule', 'ROLE_USER');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $firstName = $input->getArgument('firstName');
        $lastName = $input->getArgument('lastName');
        $roles = explode(',', $input->getArgument('roles'));

        // Vérifie que seul un administrateur peut créer un autre administrateur
        if (in_array('ROLE_ADMIN', $roles) && !$this->isAdminAuthenticated()) {
            $output->writeln('<error>Seuls les administrateurs peuvent créer d\'autres administrateurs.</error>');

            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setRoles($roles);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('<info>Utilisateur créé avec succès :</info>');
        $output->writeln("Email : $email");
        $output->writeln("Prénom : $firstName");
        $output->writeln("Nom : $lastName");
        $output->writeln('Rôles : ' . implode(', ', $roles));

        return Command::SUCCESS;
    }

    private function isAdminAuthenticated(): bool
    {
        // Ajoute ici la logique pour vérifier si le compte courant est admin
        // Par exemple, tu peux injecter le Security service et vérifier l'utilisateur courant
        return true; // À remplacer par la logique réelle
    }
}
