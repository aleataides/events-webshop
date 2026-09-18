<?php

declare(strict_types=1);

namespace App\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'orders')]
#[ORM\HasLifecycleCallbacks]
class Order
{
    use HasUuidId;
    use Timestampable;
    use HasFactory;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class, cascade: ['persist'])]
    private Collection $items;

    public function __construct(
        UuidInterface $id,
        #[ORM\ManyToOne(targetEntity: Affiliate::class)]
        #[ORM\JoinColumn(nullable: false)]
        private Affiliate $affiliate,
    ) {
        $this->id = $id;
        $this->items = new ArrayCollection();
    }

    public function getAffiliate(): Affiliate
    {
        return $this->affiliate;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderItem $item): void
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
        }
    }
}
