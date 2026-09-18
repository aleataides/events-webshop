<?php

declare(strict_types=1);

namespace App\Resources;

use App\Entities\Area;
use App\Entities\Price;

final class AreaResource
{
    public function __construct(private readonly Area $area)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->area->getId()->toString(),
            'name' => $this->area->getName(),
            'capacity' => $this->area->getCapacity(),
            'available' => max(0, $this->area->getAvailable()),
            'prices' => array_map(
                static fn (Price $price) => new PriceResource($price)->toArray(),
                $this->area->getPrices()->toArray(),
            ),
        ];
    }
}
