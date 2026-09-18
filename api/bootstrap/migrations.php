<?php

declare(strict_types=1);

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;

require __DIR__ . '/../vendor/autoload.php';

$entityManager = (require __DIR__ . '/entity-manager.php')();

return DependencyFactory::fromEntityManager(
    new PhpFile(__DIR__ . '/../config/migrations.config.php'),
    new ExistingEntityManager($entityManager),
);
