# API conventions

## Layering

`controller > service > repository`. Business rules never in controllers.
Resource classes (Laravel-like) are the only entity→JSON boundary — controllers
never serialize entities directly. Always return JSON. Max 2-line docblocks.
PSR-12 + Slim best practices.

## Base path & routing

`/api/{affiliateId}/...`, no version prefix. `AffiliateMiddleware` resolves the
affiliate from the route param and injects it into request context —
repositories/services stay affiliate-agnostic.

## CORS

Split web/api containers → direct cross-origin calls. CORS middleware
explicitly allows `X-Cart-Id` + `X-Request-Id` headers, `GET/POST/PATCH/DELETE`,
and the configured FE origin(s).

## Request correlation

`X-Request-Id` middleware — reuse the incoming header if present, else
generate a UUID. Injected into every log line via a Monolog processor
(alongside `affiliate_id`), echoed back in the response header.

## Logging

PSR-3 `LoggerInterface` via DI (Monolog), auto-enriched with `request_id` +
`affiliate_id` per request. No log redaction — this app has no real PII or
payment data behind any identifier (including `cart_id`), and anyone with log
access already has DB access in this deploy, so redacting adds no real risk
reduction. Keep it plain.

## Exception handling

Custom Slim error handler:

- Domain exceptions (`CartExpiredException`, `InsufficientStockException`,
  `EventNotFoundException`, etc.) → specific HTTP status + stable error code,
  logged at `warning` (expected business outcome).
- Anything else → generic `500`, logged at `error` with full stack trace +
  request_id, **no internal details leaked to the response**.
- Consistent envelope:
  ```json
  {"error": {"code": "cart_expired", "message": "Your reservation has expired.", "request_id": "..."}}
  ```

## Pagination

Cursor-based:
```json
{"data": [...], "meta": {"next_cursor": "01930...", "has_more": true}}
```
`next_cursor` = last item's UUIDv7 (or `null`), passed back as `?cursor=`.
Default page size 20, server-enforced max 100.

## Images

Runtime calls to the Images API are allowed (unlike event data). DB stores
only `image_id` + `copyright`; URL built at render time from
`IMAGES_API_BASE_URL` env var.

## Endpoints (initial set)

- `GET /api/{affiliateId}/events` — cursor-paginated list, filters: `q` (name
  search), `category`, `date_from`/`date_to`.
- `GET /api/{affiliateId}/events/{eventId}` — detail, incl. areas/prices.
- `GET /api/{affiliateId}/categories` — static list, only categories with ≥1
  published event, Redis-cached.
- `GET /api/{affiliateId}/cart` — current cart (via `X-Cart-Id` header, if any).
- `POST /api/{affiliateId}/cart/items` — add reservation. **Cart id is
  server-issued**: if the request has no (or an unknown/invalid) `X-Cart-Id`,
  a new `Cart` is created here and its id is returned in the response body —
  see
  [../../docs/shared/business-rules.md](../../docs/shared/business-rules.md#cart-identity).
- `PATCH /api/{affiliateId}/cart/items/{id}` — edit qty (0 = remove).
- `DELETE /api/{affiliateId}/cart/items/{id}` — remove.
- `POST /api/{affiliateId}/cart/buy` — checkout, returns order (no separate
  order-fetch endpoint).
