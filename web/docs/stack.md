# Web stack

- **Vue 3** (Composition API, `<script setup>`) + **Vite** + **Pinia** +
  **Vitest** + **Vuetify**.
- **TypeScript**: `strict: true` + `noUncheckedIndexedAccess` +
  `noImplicitOverride`.
- **Lint stack**:
  - `typescript-eslint` (`recommendedTypeChecked`)
  - `eslint-plugin-vue` (`flat/recommended` — includes `vue/attributes-order`
    built in)
  - `eslint-plugin-unused-imports` (auto-removes dead imports on `--fix`)
  - `eslint-plugin-perfectionist` (import sorting only — its
    `sort-vue-attributes` rule left disabled, redundant with
    `vue/attributes-order`)
  - Prettier run standalone (not via `eslint-plugin-prettier`)
  - All auto-fixable, wired into the pre-commit hook + CI lint job.
- **Map**: Leaflet + OpenStreetMap, lazy-loaded only when the map section is
  expanded.
- **Node 24 LTS**, npm.

## Folder structure (layer-first)

```
web/src/views/{EventList,EventDetail,Cart}/{Page}.vue + components/*.vue
web/src/components/  (shared/reusable, e.g. AppHeader.vue, TicketCounter.vue)
web/src/stores/{cart,affiliate}.ts
web/src/composables/useX.ts
web/src/router/index.ts
web/src/api/{client.ts,events.ts,cart.ts}.ts
web/src/types/{event.ts,cart.ts}.ts
web/tests/  (mirrors src/ structure)
```

- `components/` = shared/reusable across views. `views/{Page}/components/` =
  page-specific, not reused elsewhere.

## Testing scope

Unit tests (composables/stores) + component tests (Vitest + Vue Test Utils)
for key components (cart, ticket selector, filter). No E2E.

All commands run via `docker compose exec web ...` — see
[../../docs/shared/infra.md](../../docs/shared/infra.md).
