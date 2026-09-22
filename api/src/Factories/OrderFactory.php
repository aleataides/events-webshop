<?php

declare(strict_types=1);

namespace App\Factories;

use App\Entities\Order;
use Ramsey\Uuid\Uuid;

/**
 * @extends Factory<Order>
 */
final class OrderFactory extends Factory
{
    /**
     * 'affiliate' has no default — always pass it as an override.
     *
     * @return array<string, mixed>
     */
    protected function definition(): array
    {
        return [
            'id' => Uuid::uuid7(),
            'affiliate' => null,
        ];
    }
}
