<?php

declare(strict_types=1);

namespace App\Resources;

use App\Entities\TicketReservation;
use App\Shared\MoneyConvertible;

final class TicketReservationResource
{
    use MoneyConvertible;

    public function __construct(private readonly TicketReservation $reservation)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->reservation->getId()->toString(),
            'price' => new PriceResource($this->reservation->getPrice())->toArray(),
            'qty' => $this->reservation->getQty(),
            'subtotal' => $this->toMajorUnits($this->reservation->getPrice()->getValueCents() * $this->reservation->getQty()),
        ];
    }
}
