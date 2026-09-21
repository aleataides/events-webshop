# Business rules

## Cart identity

No auth in scope. **Server-issued**, not client-generated — lazy creation on
the first cart-mutating request: FE calls with no `X-Cart-Id` (nothing in
`localStorage` yet), BE creates a `Cart` row, generates the UUIDv7
server-side, returns it in the response body. FE persists it to
`localStorage`, echoes it back as `X-Cart-Id` on every subsequent call —
never a URL query param (leaks into logs/history/`Referer`; the cart id is
effectively a bearer credential). An unknown/invalid incoming `X-Cart-Id` is
treated the same as no id — BE silently issues a fresh cart rather than
erroring. Chosen over client-generation because trusting a client-picked id
means the server blindly creates-on-demand for whatever's in the header, with
no guarantee it was ever actually allocated.

## Stock locking

`Area.capacity` is the fixed original total. Available stock is always
`capacity - reserved_qty - sold_qty`. Adding a reservation is an atomic
conditional update, no long-held row locks — the check **must** include
`sold_qty`, not just `reserved_qty`, or capacity already sold would still
look free:

```sql
UPDATE area SET reserved_qty = reserved_qty + :qty
WHERE id = :area_id AND reserved_qty + sold_qty + :qty <= capacity
```

Standard high-load-ecommerce/ticketing pattern — resolves the race in one round
trip, no `SELECT ... FOR UPDATE` wait queue under contention.

**Releasing** a reservation (expiry, cart edit-down, explicit removal) must
decrement `reserved_qty` by that reservation's qty in the same transaction
that deletes/shrinks the reservation row — otherwise that stock stays
permanently (and incorrectly) marked unavailable.

The UPDATE runs inside the same DB transaction as the reservation-row write
(`CartService::transactional`), so a flush failure can't leave `reserved_qty`
incremented with no reservation to match it. That holds the row lock for the
duration of the flush, not just the UPDATE — acceptable at this scale, revisit
if cart writes get slow enough for that to matter.

## Cart expiry

- 15 minutes (`CART_EXPIRY_MINUTES` env var, default 15) — lower it locally to
  test expiry without waiting; per-event override was rejected as unneeded
  (see the seed-fixture skill).
- One clock for the **whole cart**, not per reservation row. Renewed on any
  net increase in reservations: `POST /cart/items` (new price line, cart
  empty or not) and `PATCH /cart/items/{id}` when it raises qty. A `PATCH`
  that lowers qty, or `DELETE`, never renews it — only adding more does.
- BE checks `now > cart.expires_at` on every cart read/mutation — authoritative
  over the FE's countdown timer (which is UX-only, and covers client clock
  drift / stale tabs).
- On expiry: release reservations (delete rows, freeing Area capacity), respond
  `410 Gone` + `{"error": {"code": "cart_expired", ...}}`. FE shows a modal on
  receiving this (or when its own countdown hits zero) and clears local state.
- A periodic cleanup command for long-abandoned expired carts is a
  nice-to-have, not correctness-critical (expired reservations are already
  excluded from availability queries by the `expires_at` filter regardless of
  whether the row's been deleted).

## Timezone (cart/lock timing only)

`cart.expires_at` and all lock/expiry timing is stored and compared entirely in
**UTC**:

- MariaDB: `TIMESTAMP` column + `SET time_zone = '+00:00'` forced on the DB
  connection.
- PHP: always `new DateTimeImmutable('now', new DateTimeZone('UTC'))`, never
  the bare constructor.
- Docker: `TZ=UTC` env var on both `api` and `mariadb` containers.
- API responses: `expires_at` serialized as ISO 8601 with explicit UTC offset;
  FE just diffs against `Date.now()`.

This is separate from event **display** times (`start`/`end`/`doorsOpen`),
which keep their real venue timezone (e.g. `Europe/Berlin`) for display.

## Buy / checkout

One atomic transaction converts reservation → sold, for each reservation in
the cart: **decrement `Area.reserved_qty`** (the hold is finalized, no longer
just "held") **and increment `Area.sold_qty`** by the same qty (both, or the
stock accounting double-counts that qty as unavailable), delete the
reservation row, create `Order` + `OrderItem` rows. No payment gateway — mock
checkout, Buy response IS the confirmation data (no separate
`GET /orders/{id}` endpoint).

## VAT/tax

`Price.value` already bundles `basePrice + ticketFee + outletFee`, no separate
VAT field anywhere in the source data. Matching the reference shop's own
behavior: **static disclaimer only** ("incl. VAT"), never a computed
breakdown. Total = `SUM(price.value × qty)` across cart items. No
reverse-calculated VAT line (German cultural-event tickets can use a reduced 7%
rate depending on event type — not derivable from our data, so a computed
number would be fake precision).

## Affiliate scoping

Multi-tenant, resolved via route param on both sides. Middleware (BE) injects
the affiliate into request context so repositories/services never need to know
which affiliate is asking.
