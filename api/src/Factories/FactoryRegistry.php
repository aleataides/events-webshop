<?php

declare(strict_types=1);

namespace App\Factories;

use LogicException;
use Psr\Container\ContainerInterface;

/**
 * Backs `Entity::factory()` (see HasFactory) — set once at bootstrap so
 * entities can resolve their factory without constructor-injecting it.
 */
final class FactoryRegistry
{
    private static ?ContainerInterface $container = null;

    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }

    /**
     * Convention: `App\Entities\{Name}` -> `App\Factories\{Name}Factory`.
     *
     * @template TEntity of object
     * @param class-string<TEntity> $entityClass
     * @return Factory<TEntity>
     */
    public static function resolve(string $entityClass): Factory
    {
        if (!self::$container instanceof ContainerInterface) {
            throw new LogicException('FactoryRegistry::setContainer() must be called before Entity::factory().');
        }

        $shortName = substr($entityClass, strrpos($entityClass, '\\') + 1);
        $factoryClass = __NAMESPACE__ . '\\' . $shortName . 'Factory';

        if (!is_subclass_of($factoryClass, Factory::class)) {
            throw new LogicException(sprintf('No factory registered for "%s" (expected "%s").', $entityClass, $factoryClass));
        }

        /** @var Factory<TEntity> $factory */
        $factory = self::$container->get($factoryClass);

        return $factory;
    }
}
