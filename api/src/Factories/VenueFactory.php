<?php

declare(strict_types=1);

namespace App\Factories;

use App\Entities\Venue;
use Ramsey\Uuid\Uuid;

/**
 * @extends Factory<Venue>
 */
final class VenueFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    protected function definition(): array
    {
        return [
            'id' => Uuid::uuid7(),
            'name' => $this->faker->company(),
            'street' => $this->faker->streetAddress(),
            'zipCode' => $this->faker->postcode(),
            'city' => $this->faker->city(),
            'country' => 'DE',
            'latitude' => (string) $this->faker->latitude(47, 55),
            'longitude' => (string) $this->faker->longitude(6, 15),
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected function newModel(array $attributes): Venue
    {
        return new Venue(
            $attributes['id'],
            $attributes['name'],
            $attributes['street'],
            $attributes['zipCode'],
            $attributes['city'],
            $attributes['country'],
            $attributes['latitude'],
            $attributes['longitude'],
        );
    }
}
