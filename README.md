# event-webshop

Event ticket webshop: browse events, view details, pick tickets, checkout
(mock, no payment). See [docs/task.md](docs/task.md) for the original
requirements and [docs/plan.md](docs/plan.md) for the full architecture plan.

## Quick start

Requires Docker + Docker Compose v2 (`docker compose`, not `docker-compose`).

```sh
./bin/setup
```

Copies `.env.example` → `.env` (skipped if `.env` already exists), starts the
containers, seeds the pinned affiliate, and prints the web/API URLs plus a
ready-to-open affiliate home URL. Safe to re-run for the containers/env-file
part; re-running the seed step adds more demo rows rather than replacing them
(see below) — delete the `mariadb_data` volume first (`docker compose down -v`)
for a truly clean slate.

Equivalent manual steps, if you want more control:

```sh
cp .env.example .env
docker compose up -d
docker compose exec api bin/console app:seed --no-faker   # demo data, first time only
```

Migrations run automatically on container start (`docker/api/entrypoint.sh`).
Seeding is manual — safe to re-run, but not idempotent (each run adds more
demo rows rather than replacing existing ones). `app:seed` always includes
one pinned affiliate ("ATELIER THEATER GmbH") with its own fixed event
catalog, plus Faker-generated affiliates/venues/events on top (`--affiliates`,
`--venues`, `--events`, defaults `1`/`5`/`30`). Pinned data only, no Faker:
`--no-faker`.

- Web: http://localhost:3000
- API: http://localhost:8080

The app is multi-tenant: every URL is scoped to an affiliate id
(`/:affiliateId/events`). `bin/setup` prints one for you; to look one up
manually (or after seeding Faker affiliates too):

```sh
docker compose exec mariadb sh -c 'mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" event_webshop -e "
  SELECT LOWER(CONCAT_WS('"'"'-'"'"',
    HEX(SUBSTR(id,1,4)), HEX(SUBSTR(id,5,2)), HEX(SUBSTR(id,7,2)),
    HEX(SUBSTR(id,9,2)), HEX(SUBSTR(id,11,6))
  )) AS id, name
  FROM affiliate;"'
```

## Specific event fixtures

Need a particular scenario — sold out, a specific category/venue/date/price
tiers — instead of generic Faker data. All flags optional, defaults fill in
the rest:

Sold out, otherwise all defaults:

```sh
docker compose exec api bin/console app:fixture:event --soldout
```

Specific title/category/venue/date, still sold out:

```sh
docker compose exec api bin/console app:fixture:event \
  --title="Sold Out Comedy Night" \
  --category="Comedy & Kabarett" \
  --venue-city="Berlin" \
  --start="+2 days" \
  --soldout
```

Far-future date, no other overrides:

```sh
docker compose exec api bin/console app:fixture:event --start="+3 months"
```

Tied to a specific (existing or new) affiliate:

```sh
docker compose exec api bin/console app:fixture:event \
  --affiliate="ATELIER THEATER GmbH" --title="Members-Only Preview"
```

Custom areas/prices via `--areas` (JSON) — multiple tiers, each with its own
capacity and price(s):

```sh
docker compose exec api bin/console app:fixture:event \
  --title="Multi-Tier Concert" \
  --areas='[{"name":"Innenraum","capacity":50,"prices":[{"name":"Normalpreis","value":23.76}]},{"name":"Balkon","capacity":30,"prices":[{"name":"Premium","value":45.00}]}]'
```

In Claude Code, the `seed-fixture` skill wraps this command conversationally
— no flags to remember, just describe the scenario:

- "add a soldout comedy event next week"
- "create an event with 3 price tiers"
- "give me a concert in Berlin next month"
- "add a members-only preview event for ATELIER THEATER GmbH"
- "create a sold out event happening tomorrow"

## Repo structure

- `web/` — Vue 3 + TypeScript + Vuetify frontend.
- `api/` — PHP 8.4 + Slim 4 backend.
- `docs/shared/` — decisions relevant to both apps (domain model, business
  rules, infra).
- `web/docs/`, `api/docs/` — per-app stack/conventions reference.

## Development

All local commands run through Docker — see
[docs/shared/infra.md](docs/shared/infra.md#docker) for why:

```sh
docker compose exec web npm run <script>
docker compose exec api bin/console <command>
docker compose exec api composer <script>
```

One-time setup for the pre-commit hook: `git config core.hooksPath .githooks`.
