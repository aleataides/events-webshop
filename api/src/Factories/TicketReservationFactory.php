<?php

declare(strict_types=1);

namespace App\Factories;

use App\Entities\TicketReservation;
use Ramsey\Uuid\Uuid;

/**
 * @extends Factory<TicketReservation>
 */
final class TicketReservationFactory extends Factory
{
    /**
     * 'cart' and 'price' have no default — always pass them as overrides.
     *
     * @return array<string, mixed>
     */
    protected function definition(): array
    {
        return [
            'id' => Uuid::uuid7(),
            'cart' => null,
            'price' => null,
            'qty' => 1,
        ];
    }
}
