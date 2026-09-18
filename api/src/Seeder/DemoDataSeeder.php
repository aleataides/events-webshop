<?php

declare(strict_types=1);

namespace App\Seeder;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Coordinates the per-entity seeders in dependency order, then flushes once.
 */
final class DemoDataSeeder
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AffiliateSeeder $affiliateSeeder,
        private readonly CategorySeeder $categorySeeder,
        private readonly VenueSeeder $venueSeeder,
        private readonly EventSeeder $eventSeeder,
    ) {
    }

    /**
     * @return array{affiliates: int, categories: int, venues: int, events: int}
     */
    public function seed(int $affiliateCount, int $venueCount, int $eventCount): array
    {
        $affiliates = $this->affiliateSeeder->seed($affiliateCount);
        $categories = $this->categorySeeder->seed();
        $venues = $this->venueSeeder->seed($venueCount);
        $events = $this->eventSeeder->seed($eventCount, $affiliates, $categories, $venues);

        $this->entityManager->flush();

        return [
            'affiliates' => count($affiliates),
            'categories' => count($categories),
            'venues' => count($venues),
            'events' => count($events),
        ];
    }
}
