<?php

namespace App\Api\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Api\Resource\CreateUser;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class CreateUserProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher
    ) {
    }

    /** @param CreateUser $data */
    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): User {
        // Vérifier si l'email existe déjà
        $existingUser = $this->em->getRepository(User::class)->findOneBy(['email' => $data->email]);
        if ($existingUser) {
            throw new BadRequestHttpException('Cet email est déjà utilisé.');
        }

        // Créer un nouvel utilisateur
        $user = new User();
        $user->setEmail($data->email);

        // Hasher le mot de passe avant de l’attribuer
        $hashedPassword = $this->hasher->hashPassword($user, $data->password);
        $user->setPassword($hashedPassword);

        // Sauvegarder l'utilisateur dans la base de données
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
