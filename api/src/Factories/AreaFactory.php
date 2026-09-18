<?php

declare(strict_types=1);

namespace App\Factories;

use App\Entities\Area;
use Ramsey\Uuid\Uuid;

/**
 * @extends Factory<Area>
 */
final class AreaFactory extends Factory
{
    /**
     * 'event' has no default — always pass it as an override.
     *
     * @return array<string, mixed>
     */
    protected function definition(): array
    {
        return [
            'id' => Uuid::uuid7(),
            'event' => null,
            'name' => $this->faker->randomElement(['Freie Platzwahl', 'Innenraum', 'Parkett', 'Tribüne']),
            'capacity' => random_int(20, 200),
            'reservedQty' => 0,
            'soldQty' => 0,
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected function newModel(array $attributes): Area
    {
        return new Area(
            $attributes['id'],
            $attributes['event'],
            $attributes['name'],
            $attributes['capacity'],
            $attributes['reservedQty'],
            $attributes['soldQty'],
        );
    }
}
