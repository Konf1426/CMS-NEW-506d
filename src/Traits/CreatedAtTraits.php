<?php

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;

trait CreatedAtTraits
{
    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function setCreatedAt(): void
    {
        $this->createdAt = $this->createdAt ?? new \DateTimeImmutable();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
