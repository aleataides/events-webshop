<?php

declare(strict_types=1);

namespace App\Seeder;

use App\Entity\Area;
use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator;
use Ramsey\Uuid\Uuid;

final class AreaSeeder
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Generator $faker,
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
            $area = new Area(
                Uuid::uuid7(),
                $event,
                $this->faker->randomElement(['Freie Platzwahl', 'Innenraum', 'Parkett', 'Tribüne']),
                random_int(20, 200),
            );
            $this->entityManager->persist($area);
            $this->priceSeeder->seed($area);
            $areas[] = $area;
        }

        return $areas;
    }
}
