<?php

declare(strict_types=1);

namespace App\Resources;

use App\Entities\Area;
use App\Entities\Event;

final class EventDetailResource
{
    public function __construct(private readonly Event $event)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return new EventListResource($this->event)->toArray() + [
            'description' => $this->event->getDescription(),
            'priceInfo' => $this->event->getPriceInfo(),
            'areas' => array_map(
                static fn (Area $area) => new AreaResource($area)->toArray(),
                $this->event->getAreas()->toArray(),
            ),
        ];
    }
}
