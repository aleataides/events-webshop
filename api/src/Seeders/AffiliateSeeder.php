<?php

declare(strict_types=1);

namespace App\Seeders;

use App\Entities\Affiliate;
use Doctrine\ORM\EntityManagerInterface;

final class AffiliateSeeder
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return list<Affiliate>
     */
    public function seed(int $count): array
    {
        return Affiliate::factory()->createMany($count);
    }

    /**
     * Find-or-create so repeated `app:seed` runs keep the same pinned affiliate identity.
     */
    public function seedPinned(string $name): Affiliate
    {
        $existing = $this->entityManager->getRepository(Affiliate::class)->findOneBy(['name' => $name]);
        if ($existing instanceof Affiliate) {
            return $existing;
        }

        return Affiliate::factory()->create(['name' => $name]);
    }
}
