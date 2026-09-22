# event-webshop — Architecture Plan

Status: planning (no code written yet). This is a living document — update it whenever
a decision below changes, rather than letting the conversation be the only record.

Source requirements: [task.md](./task.md). This plan resolves every ambiguity/open
question left in task.md, based on discussion with the requester.

## Repo & docs structure

- Monorepo: `web/` (Vue) + `api/` (PHP/Slim).
- Docker: **split** `web` + `api` containers — matches task.md's current
  wording ("their own container" per app). Earlier task.md wording asked for
  one single container; this had been a deliberate deviation from that at the
  time, no longer needed once task.md was updated mid-scaffolding.
- Docs layout:
  - `docs/shared/` — business rules / domain model / anything both apps need.
  - `web/docs/` — frontend-only docs.
  - `api/docs/` — backend-only docs.
  - Root `CLAUDE.md` — pointers only, always loaded, points into `docs/shared/`.
  - `web/CLAUDE.md` / `api/CLAUDE.md` — pointers only, load only when cwd is inside
    that folder (Claude Code's natural ancestor-based CLAUDE.md loading), each
    pointing to its own `docs/` folder plus `docs/shared/`.

### Folder trees

`api/` — **layer-first**, PSR-4:

```text
api/src/Controller/Event/EventListController.php
api/src/Service/Event/EventService.php
api/src/Repository/Event/EventRepository.php
api/src/Entity/Event.php
api/src/Resource/EventResource.php
api/src/Middleware/{AffiliateMiddleware,RequestIdMiddleware,CorsMiddleware}.php
api/src/Exception/{CartExpiredException,InsufficientStockException,...}.php
api/src/Shared/{Logger,ErrorHandler}.php
api/bin/console  (console commands: seed, fixture:event, migrations)
api/tests/{Unit,Integration}/... (mirrors src/ structure)
```

`web/src/` — **layer-first**:

```text
web/src/views/{EventList,EventDetail,Cart}/{Page}.vue + components/*.vue
web/src/components/  (shared/reusable, e.g. AppHeader.vue, TicketCounter.vue)
web/src/stores/{cart,affiliate}.ts
web/src/composables/useX.ts
web/src/router/index.ts
web/src/api/{client.ts,events.ts,cart.ts}.ts
web/src/types/{event.ts,cart.ts}.ts
web/tests/  (mirrors src/ structure)
```

## Domain model

Derived from the **real** EVENTIM.Light API response (event-list and event-detail
shapes were pasted in during planning — see below), not guessed.

- **Event ↔ Category**: many-to-many (junction table) — task.md confirms
  events can have N categories.
- **Event → Area → Price**: this is the real ticket-type shape, not invented.
  - `Area` (e.g. "Freie Platzwahl") holds the **shared capacity** for a block/section.
  - `Price` (e.g. "Normalpreis", "Ermäßigt") is a priced variant within an Area —
    this is what the task calls "ticket name/type" (task.md line 92 example:
    "Freie Platzwahl - Normalpreis (1) - price" matches exactly).
  - Stock/capacity lives on **Area**, shared across its Price tiers. Cart line items
    reference a `Price` (for label/price) but decrement `Area.capacity`.
- **Event** fields: `id` (own UUIDv7 PK, not the source API's id), `title`, `subtitle`,
  `description` (HTML), `priceInfo`, `start`, `end`, `salesEnd`, `doorsOpen`,
  `doorsClose`, `status`, `eventType`, `venue_id`, `affiliate_id`,
  `image_id` + `image_copyright` (photographer name — task.md's "Image: Photographer
  name" line maps directly to the source API's `image.copyright` field).
  `minPrice`/`maxPrice` are **derived** (MIN/MAX over the event's `Price.value`),
  never stored.
- **Venue**: `id`, `name`, `street`, `zipCode`, `city`, `country`, `latitude`,
  `longitude`.
- **Affiliate**: multi-tenant. Resolved via **route param** on both sides:
  - FE: `/:affiliateId/events`, `/:affiliateId/events/:eventId` (Vue Router).
  - BE: `/api/{affiliateId}/events` (Slim route), resolved by a middleware that
    injects the affiliate into request context — repositories/services stay
    affiliate-agnostic (task.md's own suggestion).
- **IDs**: UUIDv7 everywhere — time-sortable, so it doubles directly as the
  cursor-pagination cursor (task.md explicitly allows "the uuid that allows sorting").

### Example source data (for reference — not committed as fixtures, informs the

Faker seeder's field shapes)

Event list item:

```json
{"id":"69427509d2b2055383b5c11d","title":"Sia Korthaus","start":"2026-09-18T20:00:00+02:00","end":"2026-09-18T20:00:00+02:00","salesEnd":"2026-09-18T19:59:00+02:00","categoryId":"320","category":"Comedy & Kabarett","subtitle":"Wilder Wechsel","doorsOpen":"2026-09-18T19:30:00+02:00","doorsClose":"2026-09-18T20:00:00+02:00","image":{"id":"67236e53b3c959189656e5e0","copyright":"Britta Reiffers"},"minPrice":{"value":18.59,"currency":"EUR","basePrice":17,"ticketFee":1.59,"outletFee":0},"maxPrice":{"value":23.76,"currency":"EUR","basePrice":22,"ticketFee":1.76,"outletFee":0},"status":"PUBLISHED","soldout":false,"eventType":"NORMAL","venue":{"id":"56f2f0e7e4b06c2da44e9dfc","name":"ATELIER THEATER","street":"Roonstr. 78","zipCode":"50674","city":"Köln","country":"DE","geo":{"latitude":"50.932617","longitude":"6.9355087"}},"affiliate":{"id":"5da03c56503ca200015df6cb","name":"ATELIER THEATER GmbH"}}
```

Event detail (the part that matters — ticket structure):

```json
"data": {
  "description": "<p>...</p>",
  "areas": [
    {
      "id": 4953971,
      "name": "Freie Platzwahl",
      "prices": [
        {"id": 49312274, "name": "Normalpreis", "price": {"value": 23.76, "currency": "EUR", "basePrice": 22, "ticketFee": 1.76, "outletFee": 0}},
        {"id": 49312298, "name": "Ermäßigt", "price": {"value": 18.59, "currency": "EUR", "basePrice": 17, "ticketFee": 1.59, "outletFee": 0}}
      ],
      "remaining": 20,
      "remainingContingent": 20
    }
  ],
  "priceInfo": "Unsere Ticketermäßigung gilt für SchülerInnen und StudentInnen..."
}
```

## Cart / stock concurrency

- **Cart identity**: no auth in scope. **Server-issued**, not client-generated
  — lazy creation on the first cart-mutating request: FE calls with no
  `X-Cart-Id` (nothing in `localStorage` yet), BE creates a `Cart` row,
  generates the UUIDv7 server-side, returns it in the response body. FE
  persists it to `localStorage`, echoes it back as `X-Cart-Id` on every
  subsequent call (NOT a URL query param — rejected after discussing
  log/history/`Referer` leakage risk, since the cart id is effectively a
  bearer credential). An unknown/invalid incoming `X-Cart-Id` (deleted,
  cleaned-up-expired, tampered) is treated the same as no id — BE silently
  issues a fresh cart rather than erroring. Server-issuance was chosen over
  client-generation because trusting a client-picked id means the server
  would blindly create-on-demand for whatever's in the header — no guarantee
  the id was ever actually allocated, versus a real issued credential.
- **Stock locking**: `Area.capacity` is the fixed original total. Available =
  `capacity - reserved_qty - sold_qty`. Adding a reservation is an atomic
  conditional update, no long-held row locks — check must include `sold_qty`,
  not just `reserved_qty`, or already-sold capacity would still look free:

  ```sql
  UPDATE area SET reserved_qty = reserved_qty + :qty
  WHERE id = :area_id AND reserved_qty + sold_qty + :qty <= capacity
  ```

  This is the standard high-load-ecommerce/ticketing pattern — resolves the race
  in one round trip, no `SELECT ... FOR UPDATE` wait queue under contention.
  **Releasing** a reservation (expiry, edit-down, removal) must decrement
  `reserved_qty` by that qty in the same transaction that deletes/shrinks the
  row, or that stock stays incorrectly marked unavailable forever.
- `ticket_reservation` rows reference `Price` (for label/price shown) and
  implicitly `Area` (for capacity accounting), with a **cart-level** `expires_at`
  (one clock for the whole cart, reset on every add/edit — not per-reservation-row).
- **Expiry enforcement**: BE checks `now > cart.expires_at` on every cart
  read/mutation — authoritative over the FE's countdown timer (which is UX-only,
  and guards against client clock drift / stale tabs). On expiry: release
  reservations (delete rows, decrementing `reserved_qty`, freeing Area
  capacity), respond `410 Gone` + `{"error": "cart_expired"}`. FE shows a modal
  on receiving this (or when its own countdown hits zero, whichever first) and
  clears local cart state.
  A periodic cleanup command for long-abandoned expired carts is a nice-to-have
  (garbage collection only — not correctness-critical, since expired reservations
  are already excluded from availability queries by the `expires_at` filter).
- **Buy**: one atomic transaction converts each reservation → sold:
  **decrement `Area.reserved_qty`** (hold finalized) **and increment
  `Area.sold_qty`** by the same qty — both, or the accounting double-counts
  that qty as unavailable — delete the reservation row, create `Order` +
  `OrderItem` rows. **No payment gateway** — task.md never asked for one,
  explicitly out of scope (mock checkout: Buy → order created → confirmation).
- **VAT/tax**: `price.value` (from the real source data) already bundles
  `basePrice + ticketFee + outletFee` with no separate VAT field anywhere, and
  the reference shop itself only ever shows a **static disclaimer** ("incl.
  VAT"), never a computed breakdown. We do the same: total = `SUM(price.value ×
  qty)` across cart items, displayed with a static "prices include VAT" footnote
  (task.md's "Total + VAT info" line). No reverse-calculated VAT line — German
  event tickets can qualify for a reduced 7% rate depending on event type, which
  isn't derivable from our data, so a computed number would be fake precision.
- **Timezone**: `cart.expires_at` (and all lock/expiry timing) is stored and
  compared entirely in **UTC**:
  - MariaDB: `TIMESTAMP` column + `SET time_zone = '+00:00'` forced on the DB
    connection (Doctrine DBAL connection params) — don't rely on session tz alone.
  - PHP: always `new DateTimeImmutable('now', new DateTimeZone('UTC'))`, never the
    bare constructor (which uses `date.timezone` ini setting — a drift vector).
  - Docker: `TZ=UTC` env var on both `api` and `mariadb` containers.
  - API responses: `expires_at` serialized as ISO 8601 with explicit UTC offset;
    FE just diffs it against `Date.now()`, no client-side tz math needed.
  - This is separate from event **display** times (`start`/`end`/`doorsOpen`),
    which keep their real venue timezone (e.g. `Europe/Berlin`) for display.

## Backend stack

- **MariaDB** + **full Doctrine ORM** (not DBAL-only). Reasoning: hand-hydrating
  every repository method gets repetitive/error-prone (constructor args in fixed
  order); full ORM's usual risk (lazy-loading leaking into JSON responses) is
  already neutralized because **Resource classes are mandatory anyway**
  (task.md: "laravel-like resource class for the responses") and are the *only*
  thing allowed to touch/serialize entities. Thin entities, relations
  eager-loaded explicitly per query.
- **PHP-DI** — autowiring, Slim's recommended companion container
  (task.md: "prefer DI over static").
- **PHP 8.4** (property hooks, asymmetric visibility — mature since Nov 2024).
- **PHPStan level 6** (catches real argument-type mismatches and missing
  type hints without fighting third-party type-stub imprecision),
  **PHP-CS-Fixer** (PSR-12, auto-fix on `--fix`), **PHPUnit**.
- **Redis**: event cache (cache-aside, simple TTL — no invalidation logic needed,
  since events have no admin/write path to go stale against) + rate-limit
  counters (per-IP sliding window, global on all `/api` routes, `429` +
  `Retry-After` header on breach).
- **Layering**: `controller > service > repository`, business rules never in
  controllers, Resource classes as the only entity→JSON boundary, always JSON.
- **Images**: task.md explicitly allows runtime calls to the Images API (unlike
  event data). DB stores only `image_id` + `copyright`; the actual URL is built at
  render time from an `IMAGES_API_BASE_URL` env var (not hardcoded, in case it
  changes).
- **API base path**: `/api/{affiliateId}/...`, no version prefix (single
  consumer, no versioning need at this scope).
- **CORS**: split web/api containers means direct cross-origin calls from FE to
  API (not proxied through nginx). Slim CORS middleware explicitly allows the
  `X-Cart-Id` and `X-Request-Id` custom headers, `GET/POST/PATCH/DELETE`, and
  the configured FE origin(s). FE reads the API's base URL from `VITE_API_URL`
  in `web/.env` (build-time).
- **Request correlation**: `X-Request-Id` middleware — reuse the incoming header
  if the client sent one, else generate a UUID server-side. Injected into every
  log line via a Monolog processor (alongside `affiliate_id` from request
  context), echoed back in the response header.
- **Logger service**: PSR-3 `LoggerInterface` via DI (Monolog), auto-enriched
  with `request_id` + `affiliate_id` context per request — no need to pass them
  manually at each log call site. No log redaction — considered and dropped
  (this app has no real PII/payment data behind `cart_id`; anyone with log
  access already has DB access in this deploy, so redacting it reduces no
  actual risk).
- **Exception handling**: custom Slim error handler replacing the default.
  - Domain exceptions (`CartExpiredException`, `InsufficientStockException`,
    `EventNotFoundException`, etc.) → mapped to a specific HTTP status + stable
    error code, logged at `warning` (expected business outcome, not a bug).
  - Anything else (unexpected/bugs) → generic `500`, logged at `error` with
    full stack trace + request_id, **no internal details leaked to FE**.
  - Consistent JSON error envelope everywhere, matching task.md's "always
    return json" requirement for errors too:

    ```json
    {"error": {"code": "cart_expired", "message": "Your reservation has expired.", "request_id": "..."}}
    ```

## Frontend stack

- **Vue 3** (Composition API, `<script setup>`) + **Vite** + **Pinia** (state,
  primarily cart — kept per explicit preference even though backend is source of
  truth for cart contents) + **Vitest** + **Vuetify**.
- **TypeScript**: `strict: true` + `noUncheckedIndexedAccess` + `noImplicitOverride`.
- **Lint stack**:
  - `typescript-eslint` (`recommendedTypeChecked`)
  - `eslint-plugin-vue` (`flat/recommended` — includes `vue/attributes-order`
    built in, no separate plugin needed for Vue attribute ordering)
  - `eslint-plugin-unused-imports` (auto-removes dead imports on `--fix`;
    typescript-eslint's own `no-unused-vars` only warns, doesn't delete the line)
  - `eslint-plugin-perfectionist` (import sorting only — its own
    `sort-vue-attributes` rule is deliberately left disabled, redundant with
    `vue/attributes-order`)
  - **Prettier run standalone** (not via `eslint-plugin-prettier` — current
    community consensus, avoids double-formatting conflicts, faster)
  - All auto-fixable, wired into the pre-commit hook + CI lint job.
- **Pinia convention**: `stores/cart.ts` → exports `useCartStore`.
- **Component structure**: `components/` for shared/reusable components,
  `views/{Page}/components/` for page-specific ones.
- **Map**: Leaflet + OpenStreetMap (no API key/billing setup needed), lazy-loaded
  only when the map section is expanded (task.md: "show map collapse -> lazy
  load the map").
- **Route loading state**: Vue Router navigation guards (`beforeEach`/`afterEach`)
  driving a global loading indicator — not per-route `Suspense`.
- **List rendering**: infinite scroll + cursor pagination (task.md's earlier
  wording offered "SSR or infinite scroll" as alternatives — SSR/Nuxt was
  rejected to avoid that infra complexity; task.md now just says "cursor
  pagination" directly, matching what was already chosen).
  Pagination response shape: `{data: [...], meta: {next_cursor, has_more}}` —
  `next_cursor` is the last item's UUIDv7 (or `null` if none), passed back as
  `?cursor=` on the next request. Default page size e.g. 20, server-enforced max
  100 (ignores/caps larger client-requested sizes).
- **Search/filter scope**: text search on event **name only**, plus category
  filter (dropdown/chips) and date-range filter. Explicitly NOT location search.
- **Categories endpoint**: `GET /api/{affiliateId}/categories`, only categories
  with ≥1 published event (avoids dead-end filter options), cached in Redis same
  as events. **Static list** — fetched once, independent of other active
  filters (not faceted/recomputed against the currently-selected date range;
  minor UX tradeoff accepted for simplicity — one query, no filter-endpoint
  coordination).
- **Order confirmation**: no separate `GET /orders/{id}` endpoint. The Buy
  response itself *is* the confirmation data (full order object), held in
  route/component state for the confirmation page. Simpler than a fetchable
  order-by-id endpoint, at the cost of losing the confirmation view on a hard
  refresh (acceptable for this scope).

## Infra / tooling

- **Docker**: split `web` + `api` containers + `mariadb` + `redis`, via
  `docker-compose.yml`. Matches task.md's current wording ("their own
  container" per app).
- **Node 24 LTS** (Active LTS as of Oct 2025), **npm** (not pnpm — avoids an
  extra install step for reviewers cloning the repo).
- **Reproducible installs**: `npm ci` / `composer install` driven by committed
  `package-lock.json` / `composer.lock`, used everywhere (Docker builds, CI,
  local setup) — never `npm install`/`composer update` in those contexts.
- **Pre-commit**: native git hook via `core.hooksPath` (plain bash script,
  versioned in `hooks/`) — no Husky/lint-staged dependency. One-time setup:
  `git config core.hooksPath hooks` (documented in README). Script detects
  staged files by path prefix (`web/` vs `api/`) and runs the relevant
  lint/typecheck/phpstan/test commands, blocking the commit on failure.
- **All local dev commands run through Docker**, both apps — never bare
  `npm run ...` / `bin/console ...` / `composer ...` on the host. Always
  `docker compose exec web npm run <script>` / `docker compose exec api
  bin/console ...` / `docker compose exec api composer ...`. Applies uniformly
  to: the pre-commit hook, manual commands during development, the fixture skill
  (BE), and any FE equivalent (lint/test/typecheck run via `docker compose exec
  web ...`). Keeps host Node/PHP version drift out of the picture entirely —
  whatever's in the containers is what runs, every time. **CI is the one
  exception**: GH Actions installs Node/PHP directly on the runner (faster, no
  docker-in-docker overhead) rather than going through docker-compose.
- **GH Actions**: separate path-filtered `web`/`api` jobs, running in parallel.
  - `web` job: eslint, prettier check, vue-tsc typecheck, vitest, npm audit.
  - `api` job: php-cs-fixer `--dry-run`, phpstan, phpunit, composer audit.
  - Both required status checks to merge (branch protection).
- **Migrations & seeding**: migrations run automatically on `api` container
  start (`docker/api/entrypoint.sh`). Seeding stays manual
  (`bin/console app:seed`) — not idempotent (each run adds more demo rows),
  so auto-running it on every restart would pile up duplicates.
- **`.editorconfig`**: root-level, single file (not per-app) — 4-space indent for
  `.php`, 2-space for `.ts`/`.vue`/`.json`/`.yaml`, UTF-8, LF line endings, trim
  trailing whitespace, final newline. Mirrors PSR-12 (php) and Vue ecosystem
  convention (2-space) side by side. (task.md requirement, added mid-planning.)
- **Git workflow**: Conventional Commits (`feat:`/`fix:`/`chore:`/etc, via the
  `/commit-changes` skill), branch naming `type/short-description`. One branch
  per phase/case, cut from `staging`, PR'd into `staging`; `staging` → `main`
  when ready for a release point. `main` stays always-deployable.
- **Env vars**: one root `.env` (gitignored, `.env.example` committed),
  `docker-compose.yml` passes only the relevant subset into each service's
  `environment` block. Revisited mid-scaffolding: originally split per-app to
  keep FE build vars separate from BE secrets, but that risk doesn't actually
  hold — Vite only exposes `VITE_`-prefixed vars to the client bundle
  regardless of what else is in the file — so one file is simpler with no
  real tradeoff.

## Testing scope

- **Backend**: unit tests (services) + integration tests (repositories+DB,
  endpoints via Slim's test client).
- **Test DB**: separate database name (e.g. `event_webshop_test` vs
  `event_webshop`) on the same MariaDB container/service, selected via
  `APP_ENV=test` switching the connection DSN. No separate container needed.
- **RefreshDatabase-equivalent trait** (no Laravel available, hand-built):
  wraps each integration test in a transaction, rolled back in `tearDown()`,
  keeping the DB clean between tests without truncate/reseed:

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

- **Frontend**: unit tests (composables/stores) + component tests (Vitest + Vue
  Test Utils) for key components (cart, ticket selector, filter).
- **No E2E** (Playwright/Cypress) — explicitly decided against for this
  project's size.

## Dev-fixture tooling (seeder skill)

Beyond the generic Faker seeder (bulk random data for the demo), a way to create
*specific* fixture scenarios on demand while developing (e.g. "a soldout comedy
event next week", "an event with 3 price tiers, one nearly sold out", "an event
that expires a cart in 10 seconds for testing the expiry modal"):

- **Mechanism**: console command(s) with rich flags and Faker-filled defaults for
  anything omitted — e.g.
  `docker compose exec api bin/console app:fixture:event --title="..."
  --category=Comedy --venue-city=Köln --start="+2 days" --soldout
  --areas='[{"name":"Freie Platzwahl","capacity":20,"prices":[{"name":"Normalpreis","value":23.76}]}]'
  --affiliate=<id>`. Similar commands for category/venue/affiliate if standalone
  creation is ever needed. **All `app:*` commands always run through
  `docker compose exec api ...`** — never bare `bin/console ...` on the host
  — so the command runs against the actual container's PHP/extension/DB
  environment, not a possibly-mismatched host setup. This applies uniformly:
  seeder, migrations, fixture commands, and the skill below.
- **Skill**: a project-scoped skill (lives in this repo's `.claude/skills/`, not
  personal — it's specific to this app's domain) that translates a
  natural-language fixture request into the right command invocation, so flag
  names/JSON shapes don't need to be memorized. Always shells out via
  `docker compose exec api ...`, same as above. The command is the real
  mechanism; the skill is a convenience wrapper on top, to be built once the
  console command itself exists (Phase 2/3).

## Deferred / explicitly out of scope

- Visual/layout design — task.md: "the layout will be defined later." No mockups
  or detailed layout decisions made in this plan.
- Payment gateway integration — Buy button creates a mock order, no real
  processor.
- Admin area — task.md explicitly excludes it.
- User accounts/auth — not mentioned anywhere in task.md; cart identity handled
  via `X-Cart-Id` header instead (see above).

## Implementation phases

1. **Scaffolding** — repo init, docker-compose (web/api/mariadb/redis), docs
   structure (this file + CLAUDE.md files + per-app docs), CI skeleton, git hook,
   `.editorconfig`.
2. **BE data layer** — DB schema/migrations, Doctrine entities, Faker seeder for
   Event/Area/Price/Category/Venue/Affiliate, test DB + RefreshDatabase trait,
   `app:fixture:event` console command + its dev-fixture skill (see above).
3. **BE core API** — DI wiring, affiliate middleware, repositories/services/
   controllers/resources for events list+detail, Redis cache, rate limiting,
   PHPUnit+PHPStan wired into CI.
4. **BE cart** — reservation logic (atomic stock update, cart-level expiry),
   cart CRUD endpoints, buy/checkout endpoint, tests.
5. **FE scaffolding** — Vite+Vue+Vuetify+Pinia+Router, API client (with
   `X-Cart-Id` header interceptor), affiliate route param, loading-state guards,
   ESLint/Prettier/Vitest wired.
6. **FE events list** — listing page, search+filter+category+date, infinite
   scroll/cursor pagination.
7. **FE event detail** — image+copyright, map (lazy Leaflet), description
   show-more, area/price ticket selector, add-to-cart.
8. **FE cart** — cart page, edit modal, expiry countdown + expired modal, buy
   flow (mock), header cart badge.
9. **Polish** — full test pass, CI green end-to-end, README, docker-compose
   smoke test.

## Open questions / revisit later

- None outstanding as of this writing. Log new ones here as they come up, and
  move them into the sections above once resolved.
