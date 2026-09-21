---
name: affiliate-lookup
description: Look up an affiliate's id from its name (or name from its id), or list all affiliates. Use when the user asks for an affiliate id, wants to browse the app with a given affiliate name, asks "what affiliates exist", or gives a UUID and wants to know which affiliate it is.
---

# Affiliate Lookup

Every URL in this app is scoped to an affiliate id
(`/:affiliateId/events`), but `affiliate.id` is stored as `BINARY(16)`
(Doctrine's UUID binary type) — not human-readable without conversion. All
queries run via `docker compose exec mariadb sh -c 'mariadb ...'` (see
docs/shared/infra.md#docker), never a bare `mariadb` on the host.

## Queries

**List all affiliates:**
```sh
docker compose exec mariadb sh -c 'mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" event_webshop -e "
  SELECT LOWER(CONCAT_WS('"'"'-'"'"',
    HEX(SUBSTR(id,1,4)), HEX(SUBSTR(id,5,2)), HEX(SUBSTR(id,7,2)),
    HEX(SUBSTR(id,9,2)), HEX(SUBSTR(id,11,6))
  )) AS id, name
  FROM affiliate ORDER BY name;"'
```

**Exact name → id** (case-insensitive):
```sh
docker compose exec mariadb sh -c 'mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" event_webshop -e "
  SELECT LOWER(CONCAT_WS('"'"'-'"'"',
    HEX(SUBSTR(id,1,4)), HEX(SUBSTR(id,5,2)), HEX(SUBSTR(id,7,2)),
    HEX(SUBSTR(id,9,2)), HEX(SUBSTR(id,11,6))
  )) AS id, name
  FROM affiliate WHERE LOWER(name) = LOWER('"'"'<name>'"'"');"'
```

**Fuzzy name → suggestions** (only if the exact query above returns zero
rows — substitute a keyword from the given name, not the full string, so
"Atelier" matches "ATELIER THEATER GmbH"):
```sh
docker compose exec mariadb sh -c 'mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" event_webshop -e "
  SELECT LOWER(CONCAT_WS('"'"'-'"'"',
    HEX(SUBSTR(id,1,4)), HEX(SUBSTR(id,5,2)), HEX(SUBSTR(id,7,2)),
    HEX(SUBSTR(id,9,2)), HEX(SUBSTR(id,11,6))
  )) AS id, name
  FROM affiliate WHERE name LIKE '"'"'%<keyword>%'"'"' ORDER BY name LIMIT 5;"'
```

**Id → name:**
```sh
docker compose exec mariadb sh -c 'mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" event_webshop -e "
  SELECT name FROM affiliate WHERE id = UNHEX(REPLACE('"'"'<uuid>'"'"', '"'"'-'"'"', '"'"''"'"'));"'
```

## Workflow

1. **User gave a UUID** → run the id→name query, report the name. No match →
   say so plainly, don't guess.
2. **User gave a name** → run the exact-match query first.
   - One row → report that id directly.
   - Zero rows → run the fuzzy query with the most distinctive word from
     their input (drop generic suffixes like "GmbH"/"PLC" from the search
     term, they're noisy). Report up to 5 suggestions as `name (id)`. Zero
     fuzzy matches either → say no affiliate matches, offer the full list.
3. **User asks to see all affiliates** (or gives nothing to search by) → run
   the list-all query directly, no matching step.

## Examples

Request: "what's the affiliate id for ATELIER THEATER GmbH"
→ exact match, one row, report the id directly.

Request: "get me the id for atelier theatre" (misspelled/incomplete)
→ exact match on "atelier theatre" returns zero rows → fuzzy on `%atelier%`
→ report "ATELIER THEATER GmbH (01a0c2d9-...)" as the suggestion.

Request: "list all affiliates"
→ list-all query, report the full table.

Request: "which affiliate is 01a0c2d9-c758-737d-a83e-86cc52af5306"
→ id→name query, report "ATELIER THEATER GmbH".
