<?php

declare(strict_types=1);

namespace App\Factories;

use App\Entities\OrderItem;
use Ramsey\Uuid\Uuid;

/**
 * @extends Factory<OrderItem>
 */
final class OrderItemFactory extends Factory
{
    /**
     * 'order' and 'price' have no default — always pass them as overrides.
     *
     * @return array<string, mixed>
     */
    protected function definition(): array
    {
        return [
            'id' => Uuid::uuid7(),
            'order' => null,
            'price' => null,
            'qty' => 1,
        ];
    }
}
