# API conventions

## Layering

`controller > service > repository`. Business rules never in controllers.
Resource classes (Laravel-like) are the only entity→JSON boundary — controllers
never serialize entities directly. Always return JSON. Max **4 constructor
parameters** — beyond that, extract a coordinating/facade object rather than
keep adding params. That facade itself is the accepted exception to the
limit (bundling N related collaborators is its whole job) — don't chase the
count recursively into it. PSR-12 + Slim best practices. Use PHP 8.4 syntax
where it simplifies code, e.g. `new Foo()->bar()` directly (no wrapping
parens needed for a `new` expression's methods since 8.4).

**Controllers** extend `App\Http\Controllers\Controller` and are invoked as
`__invoke(...$params): Response` — no Slim `$response` to thread through;
the base class's `$this->json($data, $status = 200)` returns an
`App\Http\JsonResponse` instead. `App\Http\Routing\ControllerInvocationStrategy`
(Slim's `InvocationStrategyInterface`, registered in `bootstrap/app.php`)
copies route placeholders into request attributes, then resolves each
`__invoke` parameter by its type-hint — order doesn't matter:

- `ServerRequestInterface` (or a `FormRequest` subclass, see below) → the
  request.
- `App\Entities\Affiliate` → the `AffiliateMiddleware`-set `affiliate`
  attribute (throws `MissingAffiliateContextException` if missing).

Any other type-hint is a `LogicException` at request time — there's no
fallback/DI container lookup here, only these two resolvers.

**Request validation** stays out of controllers via `App\Http\Requests\FormRequest`
(Laravel-style): a controller that needs a validated request type-hints a
concrete `FormRequest` subclass instead of `Request` — e.g.
`__invoke(CartItemStoreRequest $request, Affiliate $affiliate)`.
`$request->validated()` returns the checked data (throws
`InvalidRequestException` on mismatch); `$request->request()` gets back the
wrapped PSR-7 request for anything else (`$this->cartId(...)` — `Affiliate`
comes from the injected param instead). Two ways to implement it:

- Declare `rules()` (`field => 'string'|'int'`) for the common "these fields
  must be these primitive types" case — the default `validate()` checks
  `data()` (the parsed body by default, override to fold in route
  attributes) against it. Used by the `Cart/` requests.
- Override `validate()` directly for anything `rules()` can't express — a
  different format, route/query params instead of the body, optional
  fields. `EventDetailRequest`/`EventListRequest` do this, reusing
  `App\Shared\ValidatesQueryParams`'s UUID/date parsing (throws the same
  `InvalidRequestException`s a plain query-param controller would).

Live in `api/src/Http/Requests/`, mirroring `Controllers/`'s subfolders.

**Comments**: classes, methods, properties, and constants use a multi-line
docblock (`/**\n * ...\n */`, max 2 lines of content, matching
`api/tests/Support/RefreshDatabase.php`'s style) — never a single-line
`/** ... */`. Comments on statements *inside* a method/function body use
`//` instead. Add either only when something actually needs explaining — not
by default on every method, and never restating what the signature/name
already says.

## Folder structure (layer-first, PSR-4)

```
api/src/Http/          — Controllers, Requests, Routing, PSR-15 middleware, JsonResponse: the
                          request/response layer.
api/src/Services/      — business logic between controllers and repositories.
api/src/Repositories/  — Doctrine custom repos, wired via #[ORM\Entity(repositoryClass:...)].
api/src/Entities/      — Doctrine entities + shared traits (HasUuidId, Timestampable, HasFactory).
api/src/Resources/     — the only classes allowed to serialize an entity to JSON.
api/src/Factories/     — model factories (definition/make/create/createMany), used by Seeders,
                          FixtureEventCommand, and tests alike.
api/src/Seeders/       — Faker-driven bulk demo data, orchestrated by DemoDataSeeder.
api/src/Console/       — CLI commands (not pluralized — not a domain collection).
api/src/Enums/         — enums (HttpStatus, ...).
api/src/Exceptions/    — DomainException hierarchy, mapped to HTTP status/error code by ErrorHandler.
api/src/Shared/        — cross-cutting utilities that don't fit any folder above (not pluralized —
                          a grab-bag, not a domain collection).
api/routes/api.php     — route registration, kept separate from bootstrap/app.php.
api/bootstrap/         — factories that build the app's dependency graph, not settings.
api/config/            — plain settings arrays (doctrine/migrations' own config).
api/migrations/        — generated migration classes.
api/bin/console        — entrypoint: registers migrations + app.commands from the DI container.
api/public/index.php   — HTTP entrypoint: builds the container + Slim app, runs it.
api/tests/             — {Unit,Integration} mirror src/'s structure; tests/Support/ holds test infra.
api/phpunit.xml, phpstan.neon, .php-cs-fixer.php  (kept at root — each tool
  auto-discovers its config there by default; moving them would mean passing
  an explicit --config/-c flag on every invocation for no benefit)
```

## Base path & routing

`/api/{affiliateId}/...`, no version prefix. `AffiliateMiddleware` resolves the
affiliate from the route param and injects it into request context —
repositories/services stay affiliate-agnostic.

## CORS

Split web/api containers → direct cross-origin calls. CORS middleware
explicitly allows `X-Cart-Id` + `X-Request-Id` headers, `GET/POST/PATCH/DELETE`,
and the configured FE origin(s).

## Request correlation

`X-Request-Id` middleware — reuse the incoming header if present, else
generate a UUID. Injected into every log line via a Monolog processor
(alongside `affiliate_id`), echoed back in the response header.

## Logging

PSR-3 `LoggerInterface` via DI (Monolog), auto-enriched with `request_id` +
`affiliate_id` per request. No log redaction — this app has no real PII or
payment data behind any identifier (including `cart_id`), and anyone with log
access already has DB access in this deploy, so redacting adds no real risk
reduction. Keep it plain.

## Exception handling

Custom Slim error handler:

- Domain exceptions (`CartExpiredException`, `InsufficientStockException`,
  `EventNotFoundException`, etc.) → specific HTTP status + stable error code,
  logged at `warning` (expected business outcome).
- Anything else → generic `500`, logged at `error` with full stack trace +
  request_id, **no internal details leaked to the response**.
- Consistent envelope:
  ```json
  {"error": {"code": "cart_expired", "message": "Your reservation has expired.", "request_id": "..."}}
  ```

## Pagination

Cursor-based:
```json
{"data": [...], "meta": {"next_cursor": "01930...", "has_more": true}}
```
`next_cursor` = last item's UUIDv7 (or `null`), passed back as `?cursor=`.
Default page size 20, server-enforced max 100.

## Images

Runtime calls to the Images API are allowed (unlike event data). DB stores
only `image_id` + `copyright`; URL built at render time from
`IMAGES_API_BASE_URL` env var.

## Endpoints (initial set)

- `GET /api/{affiliateId}` — affiliate identity (`id`, `name`), for FE display
  (e.g. header branding).
- `GET /api/{affiliateId}/events` — cursor-paginated list, filters: `q` (name
  search), `category` (comma-separated category ids, OR-matched), `date_from`/`date_to`.
- `GET /api/{affiliateId}/events/{eventId}` — detail, incl. areas/prices.
- `GET /api/{affiliateId}/categories` — static list, only categories with ≥1
  published event, Redis-cached.
- `GET /api/{affiliateId}/cart` — current cart (via `X-Cart-Id` header, if any).
- `POST /api/{affiliateId}/cart/items` — add reservation. **Cart id is
  server-issued**: if the request has no (or an unknown/invalid) `X-Cart-Id`,
  a new `Cart` is created here and its id is returned in the response body —
  see
  [../../docs/shared/business-rules.md](../../docs/shared/business-rules.md#cart-identity).
- `PATCH /api/{affiliateId}/cart/items/{id}` — edit qty (0 = remove).
- `DELETE /api/{affiliateId}/cart/items/{id}` — remove.
- `POST /api/{affiliateId}/cart/buy` — checkout, returns order (no separate
  order-fetch endpoint).
