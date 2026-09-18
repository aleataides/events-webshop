<?php

declare(strict_types=1);

namespace App\Seeders;

use App\Entities\Venue;
use App\Factories\VenueFactory;

final class VenueSeeder
{
    public function __construct(private readonly VenueFactory $venueFactory)
    {
    }

    /**
     * @return list<Venue>
     */
    public function seed(int $count): array
    {
        $venues = [];
        for ($i = 0; $i < $count; $i++) {
            $venues[] = $this->venueFactory->create();
        }

        return $venues;
    }
}
