<?php

declare(strict_types=1);

namespace App\Resources;

use App\Entities\Venue;
use JsonSerializable;

final class VenueResource implements JsonSerializable
{
    public function __construct(private readonly Venue $venue)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->venue->getId()->toString(),
            'name' => $this->venue->getName(),
            'street' => $this->venue->getStreet(),
            'zipCode' => $this->venue->getZipCode(),
            'city' => $this->venue->getCity(),
            'country' => $this->venue->getCountry(),
            'geo' => [
                'latitude' => $this->venue->getLatitude(),
                'longitude' => $this->venue->getLongitude(),
            ],
        ];
    }
}
