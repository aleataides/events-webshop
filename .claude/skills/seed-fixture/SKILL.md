---
name: seed-fixture
description: Create one specific event fixture (soldout, particular category/venue/date/price tiers) for local dev, beyond the generic Faker seeder. Use when the user describes a specific test scenario they want data for, e.g. "add a soldout comedy event next week" or "an event with 3 price tiers".
---

# Seed Fixture

Translates a natural-language fixture request into an invocation of
`app:fixture:event` — always via
`docker compose exec api bin/console app:fixture:event [flags]`, never bare
`php`/`bin/console` on the host (see docs/shared/infra.md#docker).

## Available flags

- `--title="..."` — event title. Faker default if omitted.
- `--category="..."` — category name, found or created. Default "Comedy & Kabarett".
- `--venue-city="..."` — venue city, found or created. Faker default if omitted.
- `--affiliate="..."` — affiliate name, found or created. Faker default if omitted.
- `--start="+2 days"` — relative date string (anything `strtotime`/
  `DateTimeImmutable` accepts). Default `+1 week`.
- `--soldout` — no value; sets every area's `reserved_qty`/`sold_qty` so
  available capacity is 0.
- `--areas='[{"name":"...","capacity":20,"prices":[{"name":"...","value":23.76}]}]'`
  — JSON array, one entry per Area with its Price tiers. Omit for a single
  default "Freie Platzwahl" area with one "Normalpreis" price.

## Workflow

1. Read the request and map each detail to a flag above. Infer sensible
   values for anything mentioned but not an exact match (e.g. "next week" →
   `--start="+1 week"`, "3 price tiers" → build the `--areas` JSON with 3
   entries in `prices`).
2. Leave unmentioned flags out entirely — Faker fills sensible defaults, no
   need to over-specify.
3. Run via `docker compose exec api bin/console app:fixture:event [flags]`.
4. Report the created event's title + id from the command's output.

## Examples

Request: "a soldout comedy event next week in Köln"
```sh
docker compose exec api bin/console app:fixture:event \
  --category="Comedy & Kabarett" --venue-city="Köln" --start="+1 week" --soldout
```

Request: "an event with 3 price tiers, one nearly sold out"
```sh
docker compose exec api bin/console app:fixture:event \
  --areas='[{"name":"Freie Platzwahl","capacity":100,"prices":[{"name":"Normalpreis","value":30},{"name":"Ermäßigt","value":22},{"name":"VIP","value":60}]},{"name":"Balkon","capacity":5,"prices":[{"name":"Normalpreis","value":25}]}]'
```
(Second area's small capacity makes it easy to sell out manually via the cart flow during testing — `--soldout` would zero it out immediately instead.)

Request: "an event that expires a cart in 10 seconds, for testing the expiry modal"
This isn't a fixture-event concern — cart expiry (15 min) is a fixed business
rule (see docs/shared/business-rules.md#cart-expiry), not per-event
configurable. Say so rather than inventing a flag that doesn't exist.
