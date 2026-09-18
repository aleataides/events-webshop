<?php

declare(strict_types=1);

namespace App\Seeders;

use App\Entities\Category;
use App\Factories\CategoryFactory;

final class CategorySeeder
{
    /**
     * Real category names from the EVENTIM.Light reference data (docs/plan.md).
     */
    private const array NAMES = [
        'Comedy & Kabarett', 'Konzert', 'Theater', 'Sport', 'Familie', 'Party',
    ];

    public function __construct(private readonly CategoryFactory $categoryFactory)
    {
    }

    /**
     * @return list<Category>
     */
    public function seed(): array
    {
        $categories = [];
        foreach (self::NAMES as $name) {
            $categories[] = $this->categoryFactory->create(['name' => $name]);
        }

        return $categories;
    }
}
