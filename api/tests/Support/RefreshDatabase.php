<?php

declare(strict_types=1);

namespace Tests\Support;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Wraps each test in a rolled-back transaction; provisions the test schema once per run.
 */
trait RefreshDatabase
{
    private static bool $schemaReady = false;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = (require __DIR__ . '/../../bootstrap/entity-manager.php')();

        if (!self::$schemaReady) {
            $this->provisionSchema();
            self::$schemaReady = true;
        }

        $this->entityManager->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->entityManager->getConnection()->rollBack();
        parent::tearDown();
    }

    /**
     * The test DB itself + app-user grant are provisioned once at MariaDB container init — see docker/mariadb/init.
     */
    private function provisionSchema(): void
    {
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }
}
