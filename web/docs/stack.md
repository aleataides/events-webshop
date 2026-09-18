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
  - Prettier run standalone (not via `eslint-plugin-prettier`) +
    `eslint-config-prettier` (disables every stylistic ESLint rule that could
    fight Prettier's own formatting choices)
  - All auto-fixable, wired into the pre-commit hook + CI lint job.
- **Map**: Leaflet + OpenStreetMap, lazy-loaded only when the map section is
  expanded.
- **DOMPurify**: sanitizes the event description HTML before `v-html`
  (BE-sourced, but sanitized as defense-in-depth rather than trusted blindly).
- **Node 24 LTS**, npm.

## Folder structure (layer-first)

- `views/` — one folder per page (`EventList`, `EventDetail`, `Cart`), each
  with its own `components/` for page-specific, non-reused pieces.
- `components/` — shared/reusable components across views.
- `stores/` — Pinia stores.
- `composables/` — `useX` composables.
- `router/` — Vue Router setup.
- `api/` — Axios client + per-resource API modules.
- `lib/` — small framework-agnostic helpers.
- `plugins/` — third-party plugin setup (Vuetify).
- `styles/` — global CSS/design tokens.
- `types/` — shared TypeScript types.
- `tests/` — mirrors `src/`.

## Testing scope

Unit tests (composables/stores) + component tests (Vitest + Vue Test Utils)
for key components (cart, ticket selector, filter). No E2E.

All commands run via `docker compose exec web ...` — see
[../../docs/shared/infra.md](../../docs/shared/infra.md).
