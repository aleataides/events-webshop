<?php

declare(strict_types=1);

namespace App\Resources;

use App\Entities\Category;
use App\Entities\Event;
use App\Shared\MoneyConvertible;
use DateTimeInterface;

final class EventListResource
{
    use MoneyConvertible;

    public function __construct(private readonly Event $event)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        [$minCents, $maxCents, $soldOut, $currency] = $this->priceStats();

        return [
            'id' => $this->event->getId()->toString(),
            'title' => $this->event->getTitle(),
            'subtitle' => $this->event->getSubtitle(),
            'start' => $this->event->getStart()->format(DateTimeInterface::ATOM),
            'end' => $this->event->getEnd()->format(DateTimeInterface::ATOM),
            'salesEnd' => $this->event->getSalesEnd()->format(DateTimeInterface::ATOM),
            'doorsOpen' => $this->event->getDoorsOpen()?->format(DateTimeInterface::ATOM),
            'doorsClose' => $this->event->getDoorsClose()?->format(DateTimeInterface::ATOM),
            'status' => $this->event->getStatus(),
            'eventType' => $this->event->getEventType(),
            'soldout' => $soldOut,
            'image' => [
                'id' => $this->event->getImageId(),
                'copyright' => $this->event->getImageCopyright(),
            ],
            'minPrice' => $minCents !== null ? $this->toMajorUnits($minCents) : null,
            'maxPrice' => $maxCents !== null ? $this->toMajorUnits($maxCents) : null,
            'currency' => $currency,
            'venue' => new VenueResource($this->event->getVenue())->toArray(),
            'categories' => array_map(
                static fn (Category $category) => new CategoryResource($category)->toArray(),
                $this->event->getCategories()->toArray(),
            ),
        ];
    }

    /**
     * @return array{0: ?int, 1: ?int, 2: bool, 3: ?string}
     */
    private function priceStats(): array
    {
        $cents = [];
        $available = 0;
        $hasAreas = false;
        $currency = null;

        foreach ($this->event->getAreas() as $area) {
            $hasAreas = true;
            $available += max(0, $area->getAvailable());
            foreach ($area->getPrices() as $price) {
                $cents[] = $price->getValueCents();
                $currency ??= $price->getCurrency();
            }
        }

        return [
            $cents === [] ? null : min($cents),
            $cents === [] ? null : max($cents),
            $hasAreas && $available <= 0,
            $currency,
        ];
    }
}
