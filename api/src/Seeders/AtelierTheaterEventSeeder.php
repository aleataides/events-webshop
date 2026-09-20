<?php

declare(strict_types=1);

namespace App\Seeders;

use App\Entities\Affiliate;
use App\Entities\Area;
use App\Entities\Category;
use App\Entities\Event;
use App\Entities\Price;
use App\Entities\Venue;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Seeds the pinned Atelier Theater affiliate from the real catalog snapshot
 * in Data/AtelierTheaterEvents.php, instead of Faker. See seed-fixture skill.
 */
final class AtelierTheaterEventSeeder
{
    private const int AREA_CAPACITY = 80;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param list<Category> $categories categories already resolved by CategorySeeder — reused
     *                                    by name instead of re-querying, since a same-named
     *                                    category created there is still unflushed and thus
     *                                    invisible to a find-or-create DB lookup here (there's a
     *                                    single flush() at the very end of DemoDataSeeder::seed())
     * @return list<Event>
     */
    public function seed(Affiliate $affiliate, array $categories): array
    {
        /** @var list<array{title: string, subtitle: string, category: string, venueName: string, venueStreet: string, venueZipCode: string, venueCity: string, start: string, soldout: bool, normalpreis: int, ermaessigt: int}> $rows */
        $rows = require __DIR__ . '/Data/AtelierTheaterEvents.php';

        $venue = $this->findOrCreateVenue($rows[0]);
        $categoriesByName = array_combine(array_map(static fn (Category $c) => $c->getName(), $categories), $categories);

        $events = [];
        foreach ($rows as $row) {
            $start = new DateTimeImmutable($row['start'], new DateTimeZone('UTC'));

            $event = Event::factory()->create([
                'title' => $row['title'],
                'subtitle' => $row['subtitle'],
                'venue' => $venue,
                'affiliate' => $affiliate,
                'start' => $start,
                'end' => $start->modify('+3 hours'),
                'salesEnd' => $start->modify('-1 minute'),
                'doorsOpen' => $start->modify('-30 minutes'),
                'doorsClose' => $start,
            ]);
            $event->addCategory($categoriesByName[$row['category']]);

            $area = Area::factory()->create([
                'event' => $event,
                'name' => 'Freie Platzwahl',
                'capacity' => self::AREA_CAPACITY,
                'soldQty' => $row['soldout'] ? self::AREA_CAPACITY : 0,
            ]);
            $this->addPrice($area, 'Normalpreis', $row['normalpreis']);
            $this->addPrice($area, 'Ermäßigt', $row['ermaessigt']);

            $events[] = $event;
        }

        return $events;
    }

    private function addPrice(Area $area, string $name, int $basePrice): void
    {
        $basePriceCents = $basePrice * 100;
        Price::factory()->create([
            'area' => $area,
            'name' => $name,
            'basePriceCents' => $basePriceCents,
            'ticketFeeCents' => (int) round($basePriceCents * 0.08),
        ]);
    }

    /**
     * @param array{venueName: string, venueStreet: string, venueZipCode: string, venueCity: string} $row
     */
    private function findOrCreateVenue(array $row): Venue
    {
        $existing = $this->entityManager->getRepository(Venue::class)->findOneBy(['name' => $row['venueName']]);
        if ($existing instanceof Venue) {
            return $existing;
        }

        return Venue::factory()->create([
            'name' => $row['venueName'],
            'street' => $row['venueStreet'],
            'zipCode' => $row['venueZipCode'],
            'city' => $row['venueCity'],
        ]);
    }
}
