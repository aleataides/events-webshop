<?php

declare(strict_types=1);

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\ORMSetup;
use Ramsey\Uuid\Doctrine\UuidBinaryType;

return static function (): EntityManager {
    if (!Type::hasType(UuidBinaryType::NAME)) {
        Type::addType(UuidBinaryType::NAME, UuidBinaryType::class);
    }

    $appEnv = getenv('APP_ENV') ?: 'local';

    $config = ORMSetup::createAttributeMetadataConfiguration(
        paths: [__DIR__ . '/../src/Entities'],
        isDevMode: $appEnv !== 'production',
    );
    $config->enableNativeLazyObjects(true);
    // Column names default to the property name verbatim otherwise (camelCase).
    $config->setNamingStrategy(new UnderscoreNamingStrategy(CASE_LOWER));

    $dbName = getenv('DB_NAME') ?: 'event_webshop';
    if ($appEnv === 'test') {
        $dbName .= '_test';
    }

    $connection = DriverManager::getConnection([
        'driver' => 'pdo_mysql',
        'host' => getenv('DB_HOST') ?: 'mariadb',
        'dbname' => $dbName,
        'user' => getenv('DB_USER') ?: 'event_webshop',
        'password' => getenv('DB_PASSWORD') ?: 'event_webshop',
        'charset' => 'utf8mb4',
    ], $config);

    // See docs/shared/business-rules.md#timezone — all timing stays UTC.
    $connection->executeStatement("SET time_zone = '+00:00'");

    return new EntityManager($connection, $config);
};
