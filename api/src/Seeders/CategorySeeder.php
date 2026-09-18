<?php

declare(strict_types=1);

namespace App\Seeders;

use App\Entities\Category;

final class CategorySeeder
{
    /**
     * Real category names from the EVENTIM.Light reference data (docs/plan.md).
     */
    private const array NAMES = [
        'Comedy & Kabarett', 'Konzert', 'Theater', 'Sport', 'Familie', 'Party',
    ];

    /**
     * @return list<Category>
     */
    public function seed(): array
    {
        return array_map(
            static fn (string $name) => Category::factory()->create(['name' => $name]),
            self::NAMES,
        );
    }
}
