<?php

declare(strict_types=1);

namespace App\Entities;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'cart')]
#[ORM\HasLifecycleCallbacks]
class Cart
{
    use HasUuidId;
    use Timestampable;
    use HasFactory;

    /**
     * Whole-cart expiry clock — task.md's fixed 15-minute rule.
     */
    private const int EXPIRY_MINUTES = 15;

    /**
     * @var Collection<int, TicketReservation>
     */
    #[ORM\OneToMany(mappedBy: 'cart', targetEntity: TicketReservation::class, cascade: ['persist'])]
    private Collection $reservations;

    public function __construct(
        UuidInterface $id,
        #[ORM\ManyToOne(targetEntity: Affiliate::class)]
        #[ORM\JoinColumn(nullable: false)]
        private Affiliate $affiliate,
        #[ORM\Column]
        private DateTimeImmutable $expiresAt,
    ) {
        $this->id = $id;
        $this->reservations = new ArrayCollection();
    }

    public function getAffiliate(): Affiliate
    {
        return $this->affiliate;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isExpired(DateTimeImmutable $now): bool
    {
        return $now > $this->expiresAt;
    }

    /**
     * Resets the whole-cart clock — called on every add/edit.
     */
    public function renewExpiry(DateTimeImmutable $now): void
    {
        $this->expiresAt = $now->modify('+' . self::EXPIRY_MINUTES . ' minutes');
    }

    /**
     * @return Collection<int, TicketReservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(TicketReservation $reservation): void
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
        }
    }
}
