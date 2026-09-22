<?php

declare(strict_types=1);

namespace Tests\Support;

use PHPUnit\Framework\TestCase;

/**
 * Base for Integration tests: real (rolled-back) DB + booted Slim app +
 * factories. `RefreshDatabase::setUp` is aliased since a subclass's own
 * `setUp()` would otherwise silently shadow it (traits aren't in the
 * `parent::` chain).
 */
abstract class IntegrationTestCase extends TestCase
{
    use RefreshDatabase {
        RefreshDatabase::setUp as private refreshDatabaseSetUp;
    }
    use BootsApp;
    use UsesFactories;
    use MakesHttpRequests;

    protected function setUp(): void
    {
        $this->refreshDatabaseSetUp();
        $this->setUpFactories($this->entityManager);
    }
}
