<?php

declare(strict_types=1);

namespace App\Resources;

use App\Entities\Area;
use App\Entities\Event;
use JsonSerializable;

final class EventDetailResource implements JsonSerializable
{
    public function __construct(private readonly Event $event)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return new EventListResource($this->event)->jsonSerialize() + [
            'description' => $this->event->getDescription(),
            'priceInfo' => $this->event->getPriceInfo(),
            'areas' => array_map(
                static fn (Area $area) => new AreaResource($area),
                $this->event->getAreas()->toArray(),
            ),
        ];
    }
}
