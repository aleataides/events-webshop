<?php

declare(strict_types=1);

namespace App\Factories;

use App\Entities\Category;
use Ramsey\Uuid\Uuid;

/**
 * @extends Factory<Category>
 */
final class CategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    protected function definition(): array
    {
        return [
            'id' => Uuid::uuid7(),
            'name' => $this->faker->word(),
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected function newModel(array $attributes): Category
    {
        return new Category($attributes['id'], $attributes['name']);
    }
}
