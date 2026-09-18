<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'affiliate')]
#[ORM\HasLifecycleCallbacks]
class Affiliate
{
    use HasUuidId;
    use Timestampable;

    public function __construct(
        UuidInterface $id,
        #[ORM\Column(length: 255)]
        private string $name,
        #[ORM\Column(length: 512, nullable: true)]
        private ?string $logoUrl = null,
    ) {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }
}
