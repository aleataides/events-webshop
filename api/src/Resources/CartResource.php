<?php

declare(strict_types=1);

namespace App\Resources;

use App\Entities\Cart;
use App\Entities\TicketReservation;
use App\Shared\MoneyConvertible;
use DateTimeInterface;

final class CartResource
{
    use MoneyConvertible;

    public function __construct(private readonly Cart $cart)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $items = $this->cart->getReservations()->toArray();
        $totalCents = array_sum(array_map(
            static fn (TicketReservation $reservation) => $reservation->getPrice()->getValueCents() * $reservation->getQty(),
            $items,
        ));

        return [
            'id' => $this->cart->getId()->toString(),
            'expiresAt' => $this->cart->getExpiresAt()->format(DateTimeInterface::ATOM),
            'items' => array_map(
                static fn (TicketReservation $reservation) => new TicketReservationResource($reservation)->toArray(),
                $items,
            ),
            'total' => $this->toMajorUnits((int) $totalCents),
        ];
    }
}
