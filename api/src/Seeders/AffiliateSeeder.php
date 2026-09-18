<?php

declare(strict_types=1);

namespace App\Seeders;

use App\Entities\Affiliate;

final class AffiliateSeeder
{
    /**
     * @return list<Affiliate>
     */
    public function seed(int $count): array
    {
        return Affiliate::factory()->createMany($count);
    }
}
