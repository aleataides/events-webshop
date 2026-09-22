<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Factories\Factory;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory as FakerFactory;
use LogicException;

/**
 * Wires App\Factories\* for tests via `$this->{name}Factory` (e.g.
 * `$this->affiliateFactory`), resolved by convention — no per-factory
 * property/constructor boilerplate, and new factories need no trait edit.
 * Pass a stub EntityManager for Unit tests that only call make(); pass
 * RefreshDatabase's real one to persist/create().
 */
trait UsesFactories
{
    private ?EntityManagerInterface $factoriesEntityManager = null;

    /**
     * @var array<string, Factory<object>>
     */
    private array $resolvedFactories = [];

    protected function setUpFactories(?EntityManagerInterface $entityManager = null): void
    {
        $this->factoriesEntityManager = $entityManager ?? $this->createStub(EntityManagerInterface::class);
    }

    /**
     * @return Factory<object>
     */
    public function __get(string $name): Factory
    {
        if (isset($this->resolvedFactories[$name])) {
            return $this->resolvedFactories[$name];
        }

        if (!str_ends_with($name, 'Factory') || !$this->factoriesEntityManager instanceof EntityManagerInterface) {
            throw new LogicException(sprintf('Unknown property "%s" (call setUpFactories() first?).', $name));
        }

        $class = 'App\\Factories\\' . ucfirst($name);
        if (!is_subclass_of($class, Factory::class)) {
            throw new LogicException(sprintf('No factory class "%s" for property "%s".', $class, $name));
        }

        return $this->resolvedFactories[$name] = new $class(FakerFactory::create(), $this->factoriesEntityManager);
    }
}
