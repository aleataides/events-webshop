<?php

declare(strict_types=1);

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidBinaryType;
use Ramsey\Uuid\UuidInterface;

trait HasUuidId
{
    #[ORM\Id]
    #[ORM\Column(type: UuidBinaryType::NAME, unique: true)]
    private UuidInterface $id;

    public function getId(): UuidInterface
    {
        return $this->id;
    }
}
