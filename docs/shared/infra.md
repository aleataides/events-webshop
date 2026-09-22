# Infra & tooling

## Docker

Split `web` + `api` containers + `mariadb` + `redis`, via `docker-compose.yml`
— matches task.md's requirement for FE/BE to each run "in their own
container" (task.md was updated mid-scaffolding; earlier wording asked for
one single container, which this had been a deliberate deviation from — no
longer a deviation now).

**All local dev commands run through Docker**, both apps — never bare
`npm run ...` / `bin/console ...` / `composer ...` on the host:

```sh
docker compose exec web npm run <script>
docker compose exec api bin/console <command>
docker compose exec api composer <script>
```

Keeps host Node/PHP version drift out of the picture — whatever's in the
containers is what runs, every time. **CI is the one exception** — GH Actions
installs Node/PHP directly on the runner (faster, no docker-in-docker
overhead).

## Versions

- PHP 8.4, Node 24 LTS.
- npm (not pnpm) — no extra install step for reviewers.
- Reproducible installs: `npm ci` / `composer install`, driven by committed
  `package-lock.json` / `composer.lock`, used everywhere (Docker builds, CI,
  local setup) — never `npm install`/`composer update` in those contexts.

## Pre-commit

Native git hook via `core.hooksPath`, **not** GitHub Actions or any GitHub
feature (GitHub doesn't distribute client-side hooks — `.github/` is only for
Actions/issue-templates/CODEOWNERS). Plain bash script, versioned in the
conventional `.githooks/` folder. One-time setup:
`git config core.hooksPath .githooks`. Script detects staged files by path
prefix (`web/` vs `api/`) and runs the relevant lint/typecheck/phpstan/test
commands (via `docker compose exec`, per above), blocking the commit on
failure.

## CI (GitHub Actions)

Separate path-filtered `web`/`api` jobs, running in parallel, both required to
merge:

- `web` job: eslint, prettier check, vue-tsc typecheck, vitest, npm audit.
- `api` job: php-cs-fixer `--dry-run`, phpstan, phpunit, composer audit.

## Migrations & seeding

Migrations run automatically on `api` container start
(`docker/api/entrypoint.sh`). Seeding is manual (`docker compose exec api
bin/console app:seed`) — deliberately not auto-run on container start, since
it's not idempotent (each run adds more demo rows rather than replacing
existing ones), so auto-running it on every restart would keep piling up
duplicate data.

`app:seed` always seeds one pinned affiliate ("ATELIER THEATER GmbH") with a
fixed real-world event catalog (`api/src/Seeders/Data/AtelierTheaterEvents.php`,
extracted from a real EVENTIM.Light payload — see
[domain-model.md](domain-model.md#real-source-data-reference-only-not-committed-as-fixtures)),
find-or-created so repeat runs don't duplicate it, plus `--affiliates` (default
1) additional Faker affiliates/events via the regular seeders.

## Dev-fixture tooling (the one project-specific skill)

Beyond the generic Faker seeder, `bin/console app:fixture:event` (rich flags,
Faker-filled defaults for anything omitted) creates specific fixture scenarios
on demand (e.g. "a soldout comedy event next week"). A project-scoped skill
(`.claude/skills/`) translates natural-language fixture requests into the right
invocation. Always via `docker compose exec api ...`. This is the *only*
project-specific skill planned — the bar is "genuinely translates
multi-parameter intent into a command," which rules out most other candidates
(those are just single commands, documented here instead).

## `.editorconfig`

Root-level, single file (not per-app): 4-space indent for `.php`, 2-space for
`.ts`/`.vue`/`.json`/`.yaml`, UTF-8, LF line endings, trim trailing whitespace,
final newline.

## Git workflow

Conventional Commits (`feat:`/`fix:`/`chore:`/etc), branch naming
`type/short-description`. One branch per phase/case (see
[plan.md](../plan.md#implementation-phases)), e.g. `feat/be-data-layer`.
Branches are cut from `staging`, PR'd into `staging`; `staging` → `main` when
ready for a release point. `main` stays always-deployable.

## Env vars

One root `.env` (gitignored, `.env.example` committed) for the whole stack.
`docker-compose.yml` passes only the relevant subset into each service's
`environment` block — `web` gets `VITE_API_URL`/`VITE_IMAGES_API_BASE_URL`,
`api` gets DB/Redis config + `IMAGES_API_BASE_URL`, `mariadb` gets its
credentials. No per-app `.env` files. Earlier plan had these split to keep FE
build vars separate from BE secrets, but that risk doesn't actually exist —
Vite only exposes `VITE_`-prefixed vars to the client bundle regardless of
what else shares the file — so one file is simpler with no real tradeoff. All
values have inline defaults in `docker-compose.yml`.

## CORS

Split containers means direct cross-origin calls from FE to API (not
nginx-proxied). Slim CORS middleware explicitly allows the `X-Cart-Id` and
`X-Request-Id` custom headers, `GET/POST/PATCH/DELETE`, and the configured FE
origin(s).
