<?php

declare(strict_types=1);

namespace App\Seeder;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;

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
     * @return list<Category>
     */
    public function seed(): array
    {
        $categories = [];
        foreach (self::NAMES as $name) {
            $category = new Category(Uuid::uuid7(), $name);
            $this->entityManager->persist($category);
            $categories[] = $category;
        }

        return $categories;
    }
}
