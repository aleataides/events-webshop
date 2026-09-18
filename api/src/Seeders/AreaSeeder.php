<?php

declare(strict_types=1);

namespace App\Seeders;

use App\Entities\Area;
use App\Entities\Event;
use App\Factories\AreaFactory;

final class AreaSeeder
{
    public function __construct(
        private readonly AreaFactory $areaFactory,
        private readonly PriceSeeder $priceSeeder,
    ) {
    }

    /**
     * @return list<Area>
     */
    public function seed(Event $event, int $count): array
    {
        $areas = [];
        for ($i = 0; $i < $count; $i++) {
            $area = $this->areaFactory->create(['event' => $event]);
            $this->priceSeeder->seed($area);
            $areas[] = $area;
        }

        return $areas;
    }
}
