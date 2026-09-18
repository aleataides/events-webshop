<?php

declare(strict_types=1);

namespace App\Seeders;

use App\Entities\Area;
use App\Entities\Price;
use App\Factories\PriceFactory;
use App\Shared\MoneyConvertible;

final class PriceSeeder
{
    use MoneyConvertible;

    public function __construct(private readonly PriceFactory $priceFactory)
    {
    }

    public function seed(Area $area, string $name = 'Normalpreis', ?string $basePrice = null): Price
    {
        $overrides = ['area' => $area, 'name' => $name];
        if ($basePrice !== null) {
            $basePriceCents = $this->toMinorUnits($basePrice);
            $overrides['basePriceCents'] = $basePriceCents;
            $overrides['ticketFeeCents'] = (int) round($basePriceCents * 0.08);
        }

        return $this->priceFactory->create($overrides);
    }
}
