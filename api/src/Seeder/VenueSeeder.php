<?php

declare(strict_types=1);

namespace App\Seeder;

use App\Entity\Venue;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator;
use Ramsey\Uuid\Uuid;

final class VenueSeeder
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Generator $faker,
    ) {
    }

    /**
     * @return list<Venue>
     */
    public function seed(int $count): array
    {
        $venues = [];
        for ($i = 0; $i < $count; $i++) {
            $venue = new Venue(
                Uuid::uuid7(),
                $this->faker->company(),
                $this->faker->streetAddress(),
                $this->faker->postcode(),
                $this->faker->city(),
                'DE',
                (string) $this->faker->latitude(47, 55),
                (string) $this->faker->longitude(6, 15),
            );
            $this->entityManager->persist($venue);
            $venues[] = $venue;
        }

        return $venues;
    }
}
