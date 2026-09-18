<?php

declare(strict_types=1);

use App\Console\FixtureEventCommand;
use App\Console\SeedCommand;
use DI\ContainerBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;

$builder = new ContainerBuilder();

$builder->addDefinitions([
    EntityManager::class => static fn () => (require __DIR__ . '/entity-manager.php')(),
    EntityManagerInterface::class => static fn ($c) => $c->get(EntityManager::class),
    Generator::class => static fn () => Factory::create(),
    'app.commands' => [
        SeedCommand::class,
        FixtureEventCommand::class,
    ],
]);

return $builder->build();
