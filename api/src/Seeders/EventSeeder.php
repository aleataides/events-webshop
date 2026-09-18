<?php

declare(strict_types=1);

namespace App\Seeders;

use App\Entities\Affiliate;
use App\Entities\Category;
use App\Entities\Event;
use App\Entities\Venue;
use Faker\Generator;

final class EventSeeder
{
    public function __construct(
        private readonly Generator $faker,
        private readonly AreaSeeder $areaSeeder,
    ) {
    }

    /**
     * @param list<Affiliate> $affiliates
     * @param list<Category> $categories
     * @param list<Venue> $venues
     * @return list<Event>
     */
    public function seed(int $count, array $affiliates, array $categories, array $venues): array
    {
        $events = [];
        for ($i = 0; $i < $count; $i++) {
            $event = Event::factory()->create([
                'venue' => $this->faker->randomElement($venues),
                'affiliate' => $this->faker->randomElement($affiliates),
            ]);

            foreach ($this->faker->randomElements($categories, random_int(1, 2)) as $category) {
                $event->addCategory($category);
            }

            $this->areaSeeder->seed($event, random_int(1, 2));
            $events[] = $event;
        }

        return $events;
    }
}
