<?php

declare(strict_types=1);

namespace App\Factories;

use App\Entities\Event;
use DateTimeImmutable;
use DateTimeZone;
use Ramsey\Uuid\Uuid;

/**
 * @extends Factory<Event>
 */
final class EventFactory extends Factory
{
    /**
     * 'venue' and 'affiliate' have no default — always pass them as overrides.
     *
     * @return array<string, mixed>
     */
    protected function definition(): array
    {
        $start = DateTimeImmutable::createFromMutable(
            $this->faker->dateTimeBetween('+1 week', '+6 months'),
        )->setTimezone(new DateTimeZone('UTC'));

        return [
            'id' => Uuid::uuid7(),
            'title' => $this->faker->words(3, true),
            'subtitle' => $this->faker->optional()->sentence(4),
            'description' => '<p>' . $this->faker->paragraphs(3, true) . '</p>',
            'priceInfo' => $this->faker->optional()->sentence(12),
            'start' => $start,
            'end' => $start->modify('+3 hours'),
            'salesEnd' => $start->modify('-1 minute'),
            'doorsOpen' => $start->modify('-30 minutes'),
            'doorsClose' => $start,
            'status' => 'PUBLISHED',
            'eventType' => 'NORMAL',
            'imageId' => $this->faker->uuid(),
            'imageCopyright' => $this->faker->name(),
            'venue' => null,
            'affiliate' => null,
        ];
    }
}
