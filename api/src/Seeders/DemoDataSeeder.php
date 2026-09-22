<?php

declare(strict_types=1);

namespace App\Seeders;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Coordinates the per-entity seeders in dependency order, then flushes once.
 */
final class DemoDataSeeder
{
    private const string PINNED_AFFILIATE_NAME = 'ATELIER THEATER GmbH';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AffiliateSeeder $affiliateSeeder,
        private readonly CategorySeeder $categorySeeder,
        private readonly VenueSeeder $venueSeeder,
        private readonly EventSeeder $eventSeeder,
        private readonly AtelierTheaterEventSeeder $atelierTheaterEventSeeder,
    ) {
    }

    /**
     * @return array{affiliates: int, categories: int, venues: int, events: int}
     */
    public function seed(int $affiliateCount, int $venueCount, int $eventCount): array
    {
        $pinnedAffiliate = $this->affiliateSeeder->seedPinned(self::PINNED_AFFILIATE_NAME);
        $fakerAffiliates = $this->affiliateSeeder->seed($affiliateCount);
        $categories = $this->categorySeeder->seed();
        $venues = $this->venueSeeder->seed($venueCount);

        $pinnedEvents = $this->atelierTheaterEventSeeder->seed($pinnedAffiliate, $categories);
        $fakerEvents = $this->eventSeeder->seed($eventCount, $fakerAffiliates, $categories, $venues);

        $this->entityManager->flush();

        return [
            'affiliates' => count($fakerAffiliates) + 1,
            'categories' => count($categories),
            'venues' => count($venues),
            'events' => count($pinnedEvents) + count($fakerEvents),
        ];
    }
}
