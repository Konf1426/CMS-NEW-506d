<?php declare(strict_types=1);

namespace App\Traits;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

trait CreatedAtTraits
{
    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function setCreatedAt(): void
    {
        $this->createdAt ??= new DateTimeImmutable();
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
