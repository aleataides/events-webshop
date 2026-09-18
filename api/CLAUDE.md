# api/ — backend

PHP 8.4 + Slim 4 API. See [docs/plan.md](../docs/plan.md) for full rationale;
the docs below are the terse operational reference — keep them updated when a
decision changes.

- [docs/stack.md](docs/stack.md) — MariaDB/Doctrine ORM, PHP-DI, Redis,
  quality tools, test DB, folder structure.
- [docs/conventions.md](docs/conventions.md) — layering, base path, CORS,
  request correlation, logging, exception handling, pagination, endpoints.

Also relevant (shared with `web/`):

- [../docs/shared/domain-model.md](../docs/shared/domain-model.md)
- [../docs/shared/business-rules.md](../docs/shared/business-rules.md)
- [../docs/shared/infra.md](../docs/shared/infra.md)
