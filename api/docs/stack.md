# API stack

- **PHP 8.4**, **Slim 4**.
- **Xdebug**: always installed in the image, activated via `XDEBUG=true` in
  `.env` (translated to `xdebug.mode=debug` by `docker/api/entrypoint.sh`,
  baked into the image at `/usr/local/bin/entrypoint.sh` — outside `/app` so
  the `./api:/app` bind mount doesn't shadow it — at container start) —
  toggle with `docker compose up -d`, no image rebuild.
- **MariaDB** + **full Doctrine ORM** (not DBAL-only). Thin entities
  (attribute-mapped, minimal), relations eager-loaded explicitly per query —
  never lazy-loaded into a JSON response. Resource classes are the *only*
  thing allowed to touch/serialize entities, which is what neutralizes the
  usual lazy-load-leaking-into-JSON risk of a full ORM. Entities:
  `Affiliate`, `Venue`, `Category`, `Event`, `Area`, `Price` (Phase 2) —
  `Cart`, `TicketReservation`, `Order`, `OrderItem` land in Phase 4. Two
  shared traits: `HasUuidId` (id property/getter, `uuid_binary` type — see
  [../../docs/shared/domain-model.md](../../docs/shared/domain-model.md#ids))
  and `Timestampable` (`createdAt`/`updatedAt` via `#[ORM\PrePersist]`/
  `#[ORM\PreUpdate]` lifecycle callbacks, UTC) on every entity.
- **PHP-DI** — autowiring, no manual service registration for the common
  case; `EventRepository`/`CategoryRepository` and Redis's `ClientInterface`
  are explicit factories in `bootstrap/container.php` since they can't be
  autowired (Doctrine repos need `EntityManager::getRepository()`, Redis
  needs env-driven connection config).
- **Redis**: event/category cache (cache-aside, simple TTL — no admin/write
  path, so no invalidation logic needed) + rate-limit counters (fixed window
  per IP, global on all `/api` routes, `429` + `Retry-After` on breach).
- **Doctrine custom-type parameter gotcha**: `QueryBuilder::setParameter()`
  silently drops `UuidBinaryType` conversion unless passed explicitly
  (`setParameter('id', $uuid, UuidBinaryType::NAME)`) — applies to plain
  scalar id comparisons too, not just associations. `IN (:ids)` needs raw
  bytes + `ArrayParameterType::BINARY` for the same reason. Comparing an
  association by id uses `IDENTITY(e.affiliate) = :affiliateId`, not
  `e.affiliate = :affiliate` (binding the whole entity has the same bug).
- **Quality tools**: PHPStan **level 6** (catches real argument-type
  mismatches and missing type hints without fighting third-party type-stub
  imprecision), PHP-CS-Fixer (PSR-12, auto-fix), PHPUnit.
- **Test DB**: separate database name (`event_webshop_test`) on the same
  MariaDB service, created + granted to the app user **once, at MariaDB
  container init** via `docker/mariadb/init/10-test-db.sh`
  (`docker-entrypoint-initdb.d`, runs with MariaDB's own root internally —
  application/test code never touches root credentials). Routing is
  **env-var-based, not trait-based** — `phpunit.xml`'s
  `<env name="APP_ENV" value="test" force="true"/>` sets `APP_ENV=test`
  process-wide for the whole PHPUnit run, so any code calling
  `bootstrap/entity-manager.php`'s factory connects to the test DB regardless
  of whether a given test uses `RefreshDatabase`.
- **`Tests\Support\RefreshDatabase` trait** (hand-built, no Laravel) owns two
  narrower things: transaction-wrap + rollback per test (isolation), and
  provisioning the **schema** (tables, not the database itself — that's
  already there from container init) once per test **process** (via
  `SchemaTool`, not migrations — always matches current entity mapping,
  faster than replaying migration history), guarded by a static flag.
  Deliberately *not* in `tests/bootstrap.php` (which stays pure autoload) —
  coupling schema setup to the global bootstrap would force `Unit`-only runs
  to need DB connectivity and would recreate the whole schema on every
  invocation. Only `Integration` tests (which `use RefreshDatabase`) pay that
  cost, once. Only `Integration` tests should touch a real DB at all — if a
  `Unit` test needs one, it isn't actually a unit test.

## Folder structure (layer-first, PSR-4)

```
api/src/Controllers/{EventListController,EventDetailController,CategoryListController}.php
api/src/Controllers/{Controller,ControllerInvocationStrategy}.php  (base class +
  the Slim InvocationStrategy that invokes controllers as `(Request): Response`)
api/src/Services/{EventService,CategoryService}.php
api/src/Repositories/{EventRepository,CategoryRepository}.php  (Doctrine
  custom repos, wired via #[ORM\Entity(repositoryClass:...)] on the entity)
api/src/Entities/{Event,Area,Price,Category,Venue,Affiliate,HasUuidId,Timestampable}.php
api/src/Resources/{EventListResource,EventDetailResource,CategoryResource,VenueResource,AreaResource,PriceResource}.php
api/src/Seeders/{AffiliateSeeder,CategorySeeder,VenueSeeder,EventSeeder,AreaSeeder,PriceSeeder,DemoDataSeeder}.php
api/src/Console/{SeedCommand,FixtureEventCommand}.php  (not pluralized —
  not a collection of domain objects, matches Symfony's own convention)
api/src/Middlewares/{AffiliateMiddleware,RequestIdMiddleware,CorsMiddleware,RateLimitMiddleware}.php
api/src/Exceptions/{DomainException,EventNotFoundException,AffiliateNotFoundException,InvalidRequestException,MissingAffiliateContextException}.php
api/src/Shared/{ErrorHandler,MoneyConvertible,HttpStatus,RequestContext,RequestContextProcessor,ValidatesQueryParams}.php  (not
  pluralized — a grab-bag of cross-cutting utilities, not a domain collection)
api/routes/api.php  (registers the `/api/{affiliateId}` route group on the
  Slim app; kept separate from bootstrap/app.php so routes aren't buried
  inside app-wiring code)
api/bootstrap/{entity-manager,container,app,migrations}.php  (factories —
  build services/dependency graphs, not settings — migrations.php returns a
  Doctrine\Migrations\DependencyFactory, same shape as the others)
api/config/migrations.config.php  (doctrine/migrations' own settings array)
api/migrations/  (generated migration classes)
api/bin/console  (entrypoint: registers migrations + app.commands from the DI container)
api/public/index.php  (HTTP entrypoint: builds the container + Slim app, runs it)
api/tests/{Unit,Integration}/... (mirrors src/ structure), tests/Support/RefreshDatabase.php
api/phpunit.xml, phpstan.neon, .php-cs-fixer.php  (kept at root — each tool
  auto-discovers its config there by default; moving them would mean passing
  an explicit --config/-c flag on every invocation for no benefit)
```

## Testing scope

Unit tests (services) + integration tests (repositories+DB, endpoints via
Slim's test client). No E2E.

All commands run via `docker compose exec api ...` — see
[../../docs/shared/infra.md](../../docs/shared/infra.md).
