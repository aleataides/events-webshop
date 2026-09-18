<?php

declare(strict_types=1);

namespace App\Factories;

use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator;
use LogicException;
use ReflectionClass;

/**
 * `definition()` returns Faker defaults; `make()`/`create()` build the
 * entity via reflection (constructor params matched to attribute keys).
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
     * @param array<string, mixed> $overrides
     * @return TModel
     */
    public function make(array $overrides = []): object
    {
        $attributes = [...$this->definition(), ...$overrides];
        $entityClass = $this->entityClass();
        $constructor = new ReflectionClass($entityClass)->getConstructor();

        $args = [];
        foreach ($constructor?->getParameters() ?? [] as $parameter) {
            $name = $parameter->getName();
            if (array_key_exists($name, $attributes)) {
                $args[] = $attributes[$name];
            } elseif ($parameter->isDefaultValueAvailable()) {
                $args[] = $parameter->getDefaultValue();
            } else {
                throw new LogicException(sprintf('Missing "%s" attribute for %s.', $name, $entityClass));
            }
        }

        /** @var TModel $model */
        $model = new $entityClass(...$args);

        return $model;
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

    /**
     * @param array<string, mixed> $overrides
     * @return list<TModel>
     */
    public function createMany(int $count, array $overrides = []): array
    {
        // range(1, 0) returns [1, 0], not [] — array_fill avoids that quirk.
        return array_map(fn () => $this->create($overrides), array_fill(0, max(0, $count), null));
    }

    /**
     * Convention: `App\Factories\{Name}Factory` -> `App\Entities\{Name}`.
     *
     * @return class-string<TModel>
     */
    private function entityClass(): string
    {
        $shortName = substr(static::class, strrpos(static::class, '\\') + 1);
        $shortName = substr($shortName, 0, -strlen('Factory'));

        /** @var class-string<TModel> $entityClass */
        $entityClass = 'App\\Entities\\' . $shortName;

        return $entityClass;
    }
}
