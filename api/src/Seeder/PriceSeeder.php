<?php

declare(strict_types=1);

namespace App\Seeder;

use App\Entity\Area;
use App\Entity\Price;
use App\Shared\MoneyConvertible;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator;
use Ramsey\Uuid\Uuid;

final class PriceSeeder
{
    use MoneyConvertible;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Generator $faker,
    ) {
    }

    public function seed(Area $area, string $name = 'Normalpreis', ?string $basePrice = null): Price
    {
        $basePrice ??= (string) $this->faker->randomFloat(2, 15, 80);
        $basePriceCents = $this->toMinorUnits($basePrice);
        $ticketFeeCents = (int) round($basePriceCents * 0.08);

        $price = new Price(
            Uuid::uuid7(),
            $area,
            $name,
            $basePriceCents,
            $ticketFeeCents,
        );
        $this->entityManager->persist($price);

        return $price;
    }
}
