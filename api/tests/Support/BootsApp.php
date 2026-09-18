<?php

declare(strict_types=1);

namespace Tests\Support;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Slim\App;

/**
 * Builds the Slim app from the DI container for integration tests, reusing
 * RefreshDatabase's EntityManager so app + test share one transaction.
 */
trait BootsApp
{
    private ?App $app = null;

    protected function app(): App
    {
        if ($this->app instanceof App) {
            return $this->app;
        }

        $container = require __DIR__ . '/../../bootstrap/container.php';
        $container->set(EntityManagerInterface::class, $this->entityManager);
        $container->set(EntityManager::class, $this->entityManager);

        $this->app = (require __DIR__ . '/../../bootstrap/app.php')($container);

        return $this->app;
    }
}
