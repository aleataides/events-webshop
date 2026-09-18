<?php

declare(strict_types=1);

namespace App\Seeders;

use App\Entities\Area;
use App\Entities\Event;
use App\Entities\Price;

final class AreaSeeder
{
    /**
     * @return list<Area>
     */
    public function seed(Event $event, int $count): array
    {
        $areas = [];
        for ($i = 0; $i < $count; $i++) {
            $area = Area::factory()->create(['event' => $event]);
            Price::factory()->create(['area' => $area]);
            $areas[] = $area;
        }

        return $areas;
    }
}
