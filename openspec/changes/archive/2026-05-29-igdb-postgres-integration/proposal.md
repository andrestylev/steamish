# Proposal: IGDB + PostgreSQL Integration

## Intent

Migrate from SQLite to PostgreSQL and integrate IGDB API for live game data. SQLite-only functions (`strftime`) block production deployment; 26 hardcoded games won't scale. IGDB gives us real covers, genres, ratings, and metadata.

## Scope

### In Scope
- PostgreSQL migration: fix SQLite-only queries, update `.env`, test with PostgreSQL
- Schema normalization: `genres`, `platforms`, `tags`, `companies` tables + pivot tables
- IGDB API integration: Twitch OAuth, `php artisan igdb:sync` command, full data fetch
- Update catalog filters, admin reports, game scopes to use normalized schema

### Out of Scope
- Image downloading (store URLs only)
- IGDB webhook/subscription sync (manual sync command only)
- Preserving existing game data (seeders replaced — users preserved)

## Capabilities

### New Capabilities
- `igdb-sync`: Artisan command fetching games/genres/platforms/companies from IGDB API
- `game-normalization`: Normalized schema with genres, platforms, tags, companies and pivots

### Modified Capabilities
- `game-catalog`: Genre/platform filters change from `where('genre')` / `whereJsonContains('platforms')` to pivot joins via `whereHas`
- `seed-data`: Hardcoded game seeder replaced by IGDB sync; user seeders kept
- `admin-reports`: `strftime` → `DATE_TRUNC` for PostgreSQL; revenue-by-genre joins through pivot table

## Approach

1. **Migrations**: Add new normalized tables (`genres`, `platforms`, `tags`, `companies` + pivots), add `igdb_id` and `aggregated_rating` to `games`, deprecate `genre`/`platforms` columns
2. **IGDB Client**: Service class with Twitch OAuth, rate-limited 4 req/s, Apicalypse query builder
3. **Sync command**: `igdb:sync` fetches all entities in dependency order (genres → platforms → companies → games → covers)
4. **Query updates**: Rewrite `scopeByGenre`, `scopeByPlatform`, `revenueByGenre`, monthly sales query for PostgreSQL
5. **Factory/Seeder**: Update `GameFactory` for normalized schema; `GameSeeder` uses IGDB data

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `app/Models/Game.php` | Modified | Scopes rewrite for pivot joins; add relations |
| `app/Http/Controllers/CatalogController.php` | Modified | Genre/platform filters through pivot |
| `app/Http/Controllers/AdminReportController.php` | Modified | `strftime` → `DATE_TRUNC`; genre join via pivot |
| `database/migrations/` | New | 7 new tables + games schema changes |
| `.env` | Modified | PostgreSQL + IGDB credentials |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| IGDB API rate limits | Med | Queue sync jobs, respect 4 req/s cap, paginate |
| PostgreSQL cast/type mismatches | Low | Test all queries with `RefreshDatabase` on PostgreSQL |

## Rollback Plan

Restore SQLite `.env` backup, run `migrate:fresh --seed` with original seeders, revert migration files. Existing game data is replaced by sync anyway — no data loss beyond synced data.

## Dependencies

- PostgreSQL 16+ running locally or in Docker
- Twitch dev app credentials (`IGDB_CLIENT_ID`, `IGDB_CLIENT_SECRET`) in `.env`

## Success Criteria

- [ ] `php artisan igdb:sync` fetches 50+ games with genres, platforms, covers, companies
- [ ] Catalog filters (genre, platform, price, rating) work correctly via normalized schema
- [ ] Admin report charts render with correct data on PostgreSQL (no `strftime` errors)
