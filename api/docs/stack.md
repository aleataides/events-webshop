# API stack

- **PHP 8.4**, **Slim 4**.
- **Xdebug**: always installed in the image, activated via `XDEBUG=true` in
  `.env` (translated to `xdebug.mode=debug` by `docker-entrypoint.sh` at
  container start) — toggle with `docker compose up -d`, no image rebuild.
- **MariaDB** + **full Doctrine ORM** (not DBAL-only). Thin entities
  (attribute-mapped, minimal), relations eager-loaded explicitly per query —
  never lazy-loaded into a JSON response. Resource classes are the *only*
  thing allowed to touch/serialize entities, which is what neutralizes the
  usual lazy-load-leaking-into-JSON risk of a full ORM.
- **PHP-DI** — autowiring, no manual service registration for the common case.
- **Redis**: event cache (cache-aside, simple TTL — events have no admin/write
  path, so no invalidation logic needed) + rate-limit counters (per-IP sliding
  window, global on all `/api` routes, `429` + `Retry-After` on breach).
- **Quality tools**: PHPStan level max, PHP-CS-Fixer (PSR-12, auto-fix),
  PHPUnit.
- **Test DB**: separate database name (e.g. `event_webshop_test`) on the same
  MariaDB service, selected via `APP_ENV=test` switching the connection DSN.
- **RefreshDatabase trait** (hand-built, no Laravel): wraps each integration
  test in a transaction, rolled back in `tearDown()`:
  ```php
  trait RefreshDatabase
  {
      protected function setUp(): void
      {
          parent::setUp();
          $this->getConnection()->beginTransaction();
      }

      protected function tearDown(): void
      {
          $this->getConnection()->rollBack();
          parent::tearDown();
      }
  }
  ```

## Folder structure (layer-first, PSR-4)

```
api/src/Controller/Event/EventListController.php
api/src/Service/Event/EventService.php
api/src/Repository/Event/EventRepository.php
api/src/Entity/Event.php
api/src/Resource/EventResource.php
api/src/Middleware/{AffiliateMiddleware,RequestIdMiddleware,CorsMiddleware}.php
api/src/Exception/{CartExpiredException,InsufficientStockException,...}.php
api/src/Shared/{Logger,ErrorHandler}.php
api/bin/console.php  (console commands: seed, fixture:event, migrations)
api/tests/{Unit,Integration}/... (mirrors src/ structure)
```

## Testing scope

Unit tests (services) + integration tests (repositories+DB, endpoints via
Slim's test client). No E2E.

All commands run via `docker compose exec api ...` — see
[../../docs/shared/infra.md](../../docs/shared/infra.md).
