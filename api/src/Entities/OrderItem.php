<?php

declare(strict_types=1);

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'order_item')]
#[ORM\HasLifecycleCallbacks]
class OrderItem
{
    use HasUuidId;
    use Timestampable;
    use HasFactory;

    public function __construct(
        UuidInterface $id,
        #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'items')]
        #[ORM\JoinColumn(nullable: false)]
        private Order $order,
        #[ORM\ManyToOne(targetEntity: Price::class)]
        #[ORM\JoinColumn(nullable: false)]
        private Price $price,
        #[ORM\Column]
        private int $qty,
    ) {
        $this->id = $id;
        $order->addItem($this);
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getPrice(): Price
    {
        return $this->price;
    }

    public function getQty(): int
    {
        return $this->qty;
    }
}
