# event-webshop

Event ticket webshop: browse events, view details, pick tickets, checkout
(mock, no payment). Monorepo: `web/` (Vue frontend) + `api/` (PHP/Slim
backend). See
[docs/task.md](docs/task.md) for the original requirements and
[docs/plan.md](docs/plan.md) for the full architecture plan/rationale.

**Keep the docs updated.** When a decision below (or in any linked doc)
changes during implementation, update the relevant doc in the same change —
don't let this drift from what the code actually does. `docs/plan.md` is the
narrative record (why); `docs/shared/`, `web/docs/`, `api/docs/` are the terser
operational reference (what) — update both when a decision changes.

## Shared context (always relevant, either side)

- [docs/shared/domain-model.md](docs/shared/domain-model.md) — entities,
  derived from the real EVENTIM.Light API shape.
- [docs/shared/business-rules.md](docs/shared/business-rules.md) — cart
  identity/expiry/locking, timezone handling, VAT, affiliate scoping.
- [docs/shared/infra.md](docs/shared/infra.md) — Docker, versions, CI,
  pre-commit hook, env vars, CORS.

## Per-app context

Working inside `web/` or `api/` loads that folder's own `CLAUDE.md`
automatically (Claude Code's ancestor-based loading) — no need to reference it
from here.
