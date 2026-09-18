<?php

declare(strict_types=1);

namespace App\Factories;

use App\Entities\Price;
use App\Shared\MoneyConvertible;
use Ramsey\Uuid\Uuid;

/**
 * @extends Factory<Price>
 */
final class PriceFactory extends Factory
{
    use MoneyConvertible;

    /**
     * 'area' has no default — always pass it as an override.
     *
     * @return array<string, mixed>
     */
    protected function definition(): array
    {
        $basePriceCents = $this->toMinorUnits((string) $this->faker->randomFloat(2, 15, 80));

        return [
            'id' => Uuid::uuid7(),
            'area' => null,
            'name' => 'Normalpreis',
            'basePriceCents' => $basePriceCents,
            'ticketFeeCents' => (int) round($basePriceCents * 0.08),
            'outletFeeCents' => 0,
            'currency' => 'EUR',
        ];
    }
}
