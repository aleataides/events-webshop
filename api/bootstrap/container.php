<?php

declare(strict_types=1);

use App\Console\FixtureEventCommand;
use App\Console\SeedCommand;
use App\Entities\Event;
use App\Factories\FactoryRegistry;
use App\Repositories\EventRepository;
use App\Shared\RequestContext;
use App\Shared\RequestContextProcessor;
use DI\Container;
use DI\ContainerBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Predis\Client;
use Predis\ClientInterface;
use Psr\Log\LoggerInterface;

$builder = new ContainerBuilder();

$builder->addDefinitions([
    EntityManager::class => static fn () => (require __DIR__ . '/entity-manager.php')(),
    EntityManagerInterface::class => static fn (Container $c) => $c->get(EntityManager::class),
    Generator::class => static fn () => Factory::create(),
    ClientInterface::class => static fn () => new Client([
        'scheme' => 'tcp',
        'host' => getenv('REDIS_HOST') ?: 'redis',
        'port' => (int) (getenv('REDIS_PORT') ?: 6379),
    ]),
    RequestContext::class => static fn () => new RequestContext(),
    LoggerInterface::class => static function (Container $c) {
        $logger = new Logger('api');
        $logger->pushProcessor($c->get(RequestContextProcessor::class));
        $logger->pushHandler(new StreamHandler('php://stderr'));

        return $logger;
    },
    EventRepository::class => static fn (Container $c) => $c->get(EntityManagerInterface::class)->getRepository(Event::class),
    'app.commands' => [
        SeedCommand::class,
        FixtureEventCommand::class,
    ],
]);

$container = $builder->build();
FactoryRegistry::setContainer($container);

return $container;
