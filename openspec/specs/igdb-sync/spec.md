# IGDB Sync

## Purpose

`php artisan igdb:sync` fetches games, genres, platforms, and companies from the IGDB API via Twitch OAuth and stores them in the normalized schema.

## Requirements

### Requirement: Twitch OAuth

The client MUST obtain an access token via `client_credentials` grant before each sync session. The token SHOULD be cached for its `expires_in` duration (approx 2 months). If `IGDB_CLIENT_ID` or `IGDB_CLIENT_SECRET` are missing from `.env`, the command MUST abort with a clear error.

| # | Given | When | Then |
|---|-------|------|------|
| 1 | credentials in `.env` | `igdb:sync` runs | token obtained and cached |
| 2 | expired or missing cached token | API call needed | new token fetched transparently |
| 3 | credentials missing | command starts | error message, abort |

### Requirement: Entity Sync Order

The command MUST fetch entities in dependency order within a single invocation: genres → platforms → companies → games → covers → screenshots. Each step MUST upsert records (match on `igdb_id`), never create duplicates. Games MUST NOT be fetched before genres, platforms, and companies exist.

| # | Given | When | Then |
|---|-------|------|------|
| 1 | clean database | sync runs full cycle | all entity types populated, no orphans |
| 2 | partial data exists | sync runs again | missing entities filled, no duplicates |

### Requirement: Rate-Limited API Client

Requests MUST use POST with Apicalypse body, `Client-ID` and `Authorization` headers. The client MUST respect 4 requests/second and max 8 concurrent connections. On a 429 response, the client SHOULD back off and retry.

| # | Given | When | Then |
|---|-------|------|------|
| 1 | 5+ endpoints queued | concurrent dispatch | at most 8 in-flight, sustained rate ≤ 4/s |
| 2 | API returns 429 | request fails | client retries with exponential backoff |
| 3 | server error (5xx) | transient failure | client logs warning, continues with next batch |

### Requirement: Command Interface

The command MUST expose `--game-limit=N` to cap game fetches and `--fresh` to truncate existing data before syncing. It SHOULD output progress to stdout. The name MUST be `igdb:sync`.

| # | Given | When | Then |
|---|-------|------|------|
| 1 | no flags | `php artisan igdb:sync` | all entities synced |
| 2 | `--game-limit=50` | runs | at most 50 games stored |
| 3 | `--fresh` | runs | all IGDB tables truncated, re-synced |
