# event-webshop

Event ticket webshop: browse events, view details, pick tickets, checkout
(mock, no payment). See [docs/task.md](docs/task.md) for the original
requirements and [docs/plan.md](docs/plan.md) for the full architecture plan.

## Quick start

```sh
cp .env.example .env
docker compose up -d
docker compose exec api bin/console app:seed   # demo data, first time only
```

Migrations run automatically on container start (`docker/api/entrypoint.sh`).
Seeding is manual — safe to re-run, but not idempotent (each run adds more
demo rows rather than replacing existing ones).

- Web: http://localhost:3000
- API: http://localhost:8080

The app is multi-tenant: every URL is scoped to an affiliate id
(`/:affiliateId/events`). After seeding, get an id to browse with:

```sh
docker compose exec mariadb sh -c 'mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" event_webshop -e "
  SELECT LOWER(CONCAT_WS('"'"'-'"'"',
    HEX(SUBSTR(id,1,4)), HEX(SUBSTR(id,5,2)), HEX(SUBSTR(id,7,2)),
    HEX(SUBSTR(id,9,2)), HEX(SUBSTR(id,11,6))
  )) AS id, name
  FROM affiliate;"'
```

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
