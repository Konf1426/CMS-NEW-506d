<?php declare(strict_types=1);

namespace App\Api\Resource;

use App\Validator\UnregistredEmail;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

class CreateUser
{
    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "L'email ne peut pas être vide.")]
    #[Assert\Email(message: 'Veuillez saisir un email valide.')]
    #[UnregistredEmail(message: "L'email {{ string }} est déjà utilisé.")]
    public ?string $email = null;

    #[ORM\Column]
    public ?string $password = null;
}
