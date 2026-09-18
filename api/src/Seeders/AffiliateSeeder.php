<?php

declare(strict_types=1);

namespace App\Seeders;

use App\Entities\Affiliate;
use App\Factories\AffiliateFactory;

final class AffiliateSeeder
{
    public function __construct(private readonly AffiliateFactory $affiliateFactory)
    {
    }

    /**
     * @return list<Affiliate>
     */
    public function seed(int $count): array
    {
        $affiliates = [];
        for ($i = 0; $i < $count; $i++) {
            $affiliates[] = $this->affiliateFactory->create();
        }

        return $affiliates;
    }
}
