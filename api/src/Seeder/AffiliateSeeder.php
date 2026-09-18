<?php

declare(strict_types=1);

namespace App\Seeder;

use App\Entity\Affiliate;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator;
use Ramsey\Uuid\Uuid;

final class AffiliateSeeder
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Generator $faker,
    ) {
    }

    /**
     * @return list<Affiliate>
     */
    public function seed(int $count): array
    {
        $affiliates = [];
        for ($i = 0; $i < $count; $i++) {
            $affiliate = new Affiliate(
                Uuid::uuid7(),
                $this->faker->company() . ' GmbH',
                $this->faker->imageUrl(200, 200, 'business'),
            );
            $this->entityManager->persist($affiliate);
            $affiliates[] = $affiliate;
        }

        return $affiliates;
    }
}
