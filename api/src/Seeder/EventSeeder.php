<?php

declare(strict_types=1);

namespace App\Seeder;

use App\Entity\Affiliate;
use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Venue;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator;
use Ramsey\Uuid\Uuid;

final class EventSeeder
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
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
            $start = DateTimeImmutable::createFromMutable(
                $this->faker->dateTimeBetween('+1 week', '+6 months'),
            )->setTimezone(new DateTimeZone('UTC'));

            $event = new Event(
                Uuid::uuid7(),
                $this->faker->words(3, true),
                $this->faker->optional()->sentence(4),
                '<p>' . $this->faker->paragraphs(3, true) . '</p>',
                $this->faker->optional()->sentence(12),
                $start,
                $start->modify('+3 hours'),
                $start->modify('-1 minute'),
                $start->modify('-30 minutes'),
                $start,
                'PUBLISHED',
                'NORMAL',
                $this->faker->uuid(),
                $this->faker->name(),
                $this->faker->randomElement($venues),
                $this->faker->randomElement($affiliates),
            );

            foreach ($this->faker->randomElements($categories, random_int(1, 2)) as $category) {
                $event->addCategory($category);
            }

            $this->entityManager->persist($event);
            $this->areaSeeder->seed($event, random_int(1, 2));
            $events[] = $event;
        }

        return $events;
    }
}
