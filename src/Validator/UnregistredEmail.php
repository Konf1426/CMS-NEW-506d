<?php declare(strict_types=1);

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class UnregistredEmail extends Constraint
{
    public string $message = 'L\'email "{{ string }}" est déjà lié à un utilisateur.';

    // all configurable options must be passed to the constructor
    public function __construct(?string $message = null, ?array $groups = null, $payload = null)
    {
        parent::__construct([], $groups, $payload);

        $this->message = $message ?? $this->message;
    }
}
