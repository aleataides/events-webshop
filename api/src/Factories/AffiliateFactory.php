<?php

declare(strict_types=1);

namespace App\Factories;

use App\Entities\Affiliate;
use Ramsey\Uuid\Uuid;

/**
 * @extends Factory<Affiliate>
 */
final class AffiliateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    protected function definition(): array
    {
        return [
            'id' => Uuid::uuid7(),
            'name' => $this->faker->company() . ' GmbH',
            'logoUrl' => $this->faker->imageUrl(200, 200, 'business'),
        ];
    }
}
