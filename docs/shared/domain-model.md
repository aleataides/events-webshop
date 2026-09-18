# Domain model

Source of truth for entities shared between `web` and `api`. Derived from the
real EVENTIM.Light API response shapes (see [plan.md](../plan.md) for the raw
example payloads), not invented.

## Entities

- **Event**: `id` (UUIDv7, own PK — not the source API's id), `title`,
  `subtitle`, `description` (HTML), `priceInfo`, `start`, `end`, `salesEnd`,
  `doorsOpen`, `doorsClose`, `status`, `eventType`, `venue_id`, `affiliate_id`,
  `image_id`, `image_copyright` (photographer name).
  `minPrice`/`maxPrice` are **derived** (MIN/MAX over the event's `Price.value`
  across all its Areas), never stored.
- **Category**: `id`, `name`. **Event ↔ Category is many-to-many** (junction
  table) even though the task only confirms 1 category today.
- **Area**: `id`, `event_id`, `name` (e.g. "Freie Platzwahl"), `capacity`,
  `sold_qty`. Holds the **shared stock pool** for a block/section.
- **Price**: `id`, `area_id`, `name` (e.g. "Normalpreis", "Ermäßigt"), `value`,
  `currency`, `basePrice`, `ticketFee`, `outletFee`. This is what the task calls
  "ticket name/type" — the actual sellable/cart line item. Stock is NOT
  per-Price; adding any Price under an Area decrements that Area's shared
  capacity.
- **Venue**: `id`, `name`, `street`, `zipCode`, `city`, `country`, `latitude`,
  `longitude`.
- **Affiliate**: `id`, `name`, `logo_url`. Multi-tenant — resolved via route
  param on both sides (`/:affiliateId/...` FE, `/api/{affiliateId}/...` BE),
  injected into request context by middleware so repositories/services stay
  affiliate-agnostic.
- **Cart**: `id` (server-issued UUIDv7, lazily created on first cart mutation,
  sent as `X-Cart-Id` header thereafter — not a URL param, see
  [business-rules.md](./business-rules.md#cart-identity)), `affiliate_id`,
  `expires_at` (UTC, whole-cart clock, reset on every add/edit).
- **TicketReservation**: `cart_id`, `price_id`, `qty`. Capacity accounting
  happens against the reservation's Price's Area, not per-reservation.
- **Order** / **OrderItem**: created on Buy, from converted reservations. No
  payment gateway — mock checkout only.

## IDs

UUIDv7 everywhere — time-sortable, doubles directly as the cursor-pagination
cursor.

## Real source data (reference only, not committed as fixtures)

Event list item and event detail (with `areas`/`prices`) — see
[plan.md](../plan.md#domain-model) for the full JSON examples this schema was
derived from.
