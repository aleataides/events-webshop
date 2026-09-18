<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * Money fields are minor units (cents) — see App\Shared\MoneyConvertible for
 * decimal-string conversion at the boundaries (seeders, Resource classes).
 */
#[ORM\Entity]
#[ORM\Table(name: 'price')]
#[ORM\HasLifecycleCallbacks]
class Price
{
    use HasUuidId;
    use Timestampable;

    public function __construct(
        UuidInterface $id,
        #[ORM\ManyToOne(targetEntity: Area::class, inversedBy: 'prices')]
        #[ORM\JoinColumn(nullable: false)]
        private Area $area,
        #[ORM\Column(length: 255)]
        private string $name,
        #[ORM\Column]
        private int $basePriceCents,
        #[ORM\Column]
        private int $ticketFeeCents = 0,
        #[ORM\Column]
        private int $outletFeeCents = 0,
        #[ORM\Column(length: 3)]
        private string $currency = 'EUR',
    ) {
        $this->id = $id;
    }

    public function getArea(): Area
    {
        return $this->area;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Total charged: basePrice + ticketFee + outletFee (matches source API's "value").
     */
    public function getValueCents(): int
    {
        return $this->basePriceCents + $this->ticketFeeCents + $this->outletFeeCents;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getBasePriceCents(): int
    {
        return $this->basePriceCents;
    }

    public function getTicketFeeCents(): int
    {
        return $this->ticketFeeCents;
    }

    public function getOutletFeeCents(): int
    {
        return $this->outletFeeCents;
    }
}
