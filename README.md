# event-webshop

Event ticket webshop: browse events, view details, pick tickets, checkout
(mock, no payment). See [docs/task.md](docs/task.md) for the original
requirements and [docs/plan.md](docs/plan.md) for the full architecture plan.

## Quick start

```sh
cp .env.example .env
docker compose up -d
docker compose exec api bin/console app:seed   # auto-runs on first start too
```

- Web: http://localhost:3000
- API: http://localhost:8080

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
