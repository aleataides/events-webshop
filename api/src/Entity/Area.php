<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'area')]
#[ORM\HasLifecycleCallbacks]
class Area
{
    use HasUuidId;
    use Timestampable;

    /**
     * @var Collection<int, Price>
     */
    #[ORM\OneToMany(mappedBy: 'area', targetEntity: Price::class, cascade: ['persist'])]
    private Collection $prices;

    public function __construct(
        UuidInterface $id,
        #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'areas')]
        #[ORM\JoinColumn(nullable: false)]
        private Event $event,
        #[ORM\Column(length: 255)]
        private string $name,
        #[ORM\Column]
        private int $capacity,
        #[ORM\Column]
        private int $reservedQty = 0,
        #[ORM\Column]
        private int $soldQty = 0,
    ) {
        $this->id = $id;
        $this->prices = new ArrayCollection();
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function getReservedQty(): int
    {
        return $this->reservedQty;
    }

    public function getSoldQty(): int
    {
        return $this->soldQty;
    }

    public function getAvailable(): int
    {
        return $this->capacity - $this->reservedQty - $this->soldQty;
    }

    /**
     * @return Collection<int, Price>
     */
    public function getPrices(): Collection
    {
        return $this->prices;
    }
}
