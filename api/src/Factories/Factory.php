<?php

declare(strict_types=1);

namespace App\Factories;

use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator;

/**
 * Laravel-style model factory: `definition()` returns Faker defaults,
 * `make()` builds an unpersisted entity, `create()` also persists it.
 *
 * @template TModel of object
 */
abstract class Factory
{
    public function __construct(
        protected readonly Generator $faker,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    abstract protected function definition(): array;

    /**
     * @param array<string, mixed> $attributes
     * @return TModel
     */
    abstract protected function newModel(array $attributes): object;

    /**
     * @param array<string, mixed> $overrides
     * @return TModel
     */
    public function make(array $overrides = []): object
    {
        return $this->newModel([...$this->definition(), ...$overrides]);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return TModel
     */
    public function create(array $overrides = []): object
    {
        $model = $this->make($overrides);
        $this->entityManager->persist($model);

        return $model;
    }
}
