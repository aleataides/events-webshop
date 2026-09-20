# Web conventions

## Naming

- Components: PascalCase (`EventCard.vue`), multi-word (Vue style guide
  default, avoids HTML tag clashes).
- Pinia stores: `stores/cart.ts` → exports `useCartStore`.
- Composables: `useX.ts` camelCase, prefixed `use`.
- Everything else: kebab-case.

## State

Pinia for shared state (primarily cart). Backend is the source of truth for
cart contents (reservations, expiry) — FE store is mostly a read-through
cache of API responses, not independently-authoritative client state.

## Cart identity

**Server-issued**, not client-generated. First cart-mutating request goes out
with no `X-Cart-Id` header; the BE response includes the newly-created cart's
id, which the FE persists to `localStorage` and echoes back via an
Axios/fetch interceptor as `X-Cart-Id` on every subsequent call to
`VITE_API_URL`. Never sent as a URL param. See
[../../docs/shared/business-rules.md](../../docs/shared/business-rules.md#cart-identity)
for the full reasoning.

## Routing

Affiliate id as a route param: `/:affiliateId/events`,
`/:affiliateId/events/:eventId`. Vue Router navigation guards (`beforeEach`/
`afterEach`) drive a global loading indicator between route transitions.

## Search/filter

Text search on event **name only** (not location), plus category filter
(dropdown/chips, static list fetched once — not faceted against other active
filters) and date-range filter.

## Lists

Infinite scroll + cursor pagination — see
[../../api/docs/conventions.md](../../api/docs/conventions.md#pagination) for
the response shape.

## Lazy loading

Every route's page component is already lazy via the router's
`() => import(...)` (one JS chunk per page). Beyond that, lazy-load
(`defineAsyncComponent(() => import(...))`) only a component that's both
conditionally rendered (`v-if`, not always visible on mount) **and** pulls in
a heavy dependency — e.g. `VenueMap.vue` (behind `EventLocation.vue`'s "Show
Map" toggle, drags in `leaflet` + its CSS, ~44 kB gzipped). Don't wrap plain
Vuetify-only components (dialogs, cards) just because they're conditional —
no real dependency weight to defer, so it's pure overhead.

## Cart expiry UX

Countdown timer driven by the cart's `expires_at` (UX-only — backend is
authoritative on every mutation). On expiry (own timer hitting zero, or a
`410 cart_expired` response), show a modal and clear local cart state.
