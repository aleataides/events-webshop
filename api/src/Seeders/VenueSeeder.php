<?php

declare(strict_types=1);

namespace App\Seeders;

use App\Entities\Venue;

final class VenueSeeder
{
    /**
     * @return list<Venue>
     */
    public function seed(int $count): array
    {
        return Venue::factory()->createMany($count);
    }
}
