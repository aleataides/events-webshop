<?php

declare(strict_types=1);

namespace App\Seeders;

use App\Entities\Category;
use Doctrine\ORM\EntityManagerInterface;

final class CategorySeeder
{
    /**
     * Real category names from the EVENTIM.Light reference data (docs/plan.md).
     */
    private const array NAMES = [
        'Comedy & Kabarett', 'Konzert', 'Theater', 'Sport', 'Familie', 'Party',
    ];

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * Find-or-create: categories are reference data, not demo bulk content —
     * repeat `app:seed` runs must not duplicate them (see EventSeeder /
     * AtelierTheaterEventSeeder, which both link events to these rows).
     *
     * @return list<Category>
     */
    public function seed(): array
    {
        return array_map(fn (string $name) => $this->findOrCreate($name), self::NAMES);
    }

    private function findOrCreate(string $name): Category
    {
        $existing = $this->entityManager->getRepository(Category::class)->findOneBy(['name' => $name]);
        if ($existing instanceof Category) {
            return $existing;
        }

        return Category::factory()->create(['name' => $name]);
    }
}
