<?php

declare(strict_types=1);

namespace App\Factories;

use App\Entities\Cart;
use DateTimeImmutable;
use DateTimeZone;
use Ramsey\Uuid\Uuid;

/**
 * @extends Factory<Cart>
 */
final class CartFactory extends Factory
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
            'expiresAt' => new DateTimeImmutable('+15 minutes', new DateTimeZone('UTC')),
        ];
    }
}
