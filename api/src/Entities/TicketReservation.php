<?php

declare(strict_types=1);

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * Capacity accounting happens against price.area, not per-reservation-row —
 * see docs/shared/business-rules.md#stock-locking.
 */
#[ORM\Entity]
#[ORM\Table(name: 'ticket_reservation')]
#[ORM\HasLifecycleCallbacks]
class TicketReservation
{
    use HasUuidId;
    use Timestampable;
    use HasFactory;

    public function __construct(
        UuidInterface $id,
        #[ORM\ManyToOne(targetEntity: Cart::class, inversedBy: 'reservations')]
        #[ORM\JoinColumn(nullable: false)]
        private Cart $cart,
        #[ORM\ManyToOne(targetEntity: Price::class)]
        #[ORM\JoinColumn(nullable: false)]
        private Price $price,
        #[ORM\Column]
        private int $qty,
    ) {
        $this->id = $id;
        $cart->addReservation($this);
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function getPrice(): Price
    {
        return $this->price;
    }

    public function getQty(): int
    {
        return $this->qty;
    }

    public function setQty(int $qty): void
    {
        $this->qty = $qty;
    }
}
