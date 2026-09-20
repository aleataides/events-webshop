<?php

declare(strict_types=1);

namespace App\Resources;

use App\Entities\TicketReservation;
use App\Shared\MoneyConvertible;
use DateTimeInterface;

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
        $area = $this->reservation->getPrice()->getArea();
        $event = $area->getEvent();

        return [
            'id' => $this->reservation->getId()->toString(),
            'price' => new PriceResource($this->reservation->getPrice())->toArray(),
            'qty' => $this->reservation->getQty(),
            'subtotal' => $this->toMajorUnits($this->reservation->getPrice()->getValueCents() * $this->reservation->getQty()),
            'area' => [
                'id' => $area->getId()->toString(),
                'name' => $area->getName(),
            ],
            'event' => [
                'id' => $event->getId()->toString(),
                'title' => $event->getTitle(),
                'start' => $event->getStart()->format(DateTimeInterface::ATOM),
                'image' => [
                    'id' => $event->getImageId(),
                    'copyright' => $event->getImageCopyright(),
                ],
                'venue' => new VenueResource($event->getVenue())->toArray(),
            ],
        ];
    }
}
