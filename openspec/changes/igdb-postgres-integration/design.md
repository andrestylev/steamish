# Design: IGDB + PostgreSQL Integration

## Technical Approach

Two-phased strategy: first **schema normalization + PostgreSQL compatibility**, then **IGDB sync pipeline**.

Phase 1 — new migrations add normalized tables (`genres`, `platforms`, `tags`, `companies` + pivots), extend `games` with IGDB fields, make `genre`/`platforms` nullable. Rewrite all queries to use pivot joins and `DATE_TRUNC`. Existing test infrastructure (SQLite `:memory:`) continues working since all new SQL is cross-dialect.

Phase 2 — `IgdbClient` service class with Twitch OAuth + rate limiter (4 req/s). Single `igdb:sync` command fetches in dependency order: genres → platforms → companies → games → covers → screenshots. Seeders drop hardcoded games but keep user/review/purchase seeding.

## Architecture Decisions

### IGDB Client — Service class (not Facade)

| Option | Tradeoffs | Decision |
|--------|-----------|----------|
| Facade (static) | Testable via `Facade::shouldReceive`, but hides dependency | Rejected — hides I/O boundary |
| Service class | DI-injectable, follows existing `StripeService` pattern | **Chosen** — matches codebase convention |
| Job-based | Async but adds queue complexity | Rejected — sync command is explicit one-shot |

Service in `app/Services/IgdbClient.php`, injected into command via constructor. Handles Twitch OAuth (cache token, refresh on 401), rate limiting (4 req/s with `\Symfony\Component\RateLimiter\RateLimiterFactory`), Apicalypse query building.

### Sync Command — Single command with options

| Option | Tradeoffs | Decision |
|--------|-----------|----------|
| One command per entity | Flexible per-entity refresh, but 5+ commands to learn | Rejected — cognitive overhead |
| Single `igdb:sync` with step flags | `--skip-genres`, `--only-games` cover all cases | **Chosen** — `--game-limit=N`, `--fresh`, step filters |

Command delegates to step methods, each upserting via `igdb_id` unique key. Paginates IGDB results (500 per page). Rate-limited globally at the client level.

### strftime Replacement

| Option | Tradeoffs | Decision |
|--------|-----------|----------|
| Raw `DATE_TRUNC` in controller | PostgreSQL-only, breaks SQLite tests | Rejected — breaks CI |
| DB::connection()->getDriverName() conditional | Portable but ugly | Rejected — conditional logic in queries |
| Custom DB macro / abstracted scope | Tested once, works everywhere | **Chosen** — `Game::scopeMonthlySales($query)` macro |

The monthly sales query moves into a scope on Purchase model that uses `DATE_TRUNC('month', created_at)` for PostgreSQL and `strftime('%Y-%m', created_at)` for SQLite, detected via `DB::connection()->getDriverName()`.

### Pivot Fallback Strategy

Read queries try pivots first, fall back to legacy `genre`/`platforms` columns. This is a temporary bridge — once sync populates pivots, the fallback path is dead code we remove after one release cycle.

## Data Flow

```
IGDB API (POST /v4/games|genres|platforms|companies)
    │  Apicalypse query + Bearer token
    ▼
IgdbClient ──rate limiter──→ Twitch OAuth token (cache 60d)
    │
    ▼
igdb:sync command
    │  genres → platforms → companies → games → covers → screenshots
    ▼
Database (upsert on igdb_id)
    │
    ▼
Queries (scopeByGenre, scopeByPlatform, revenueByGenre)
    ──→ pivot joins (game_genre, game_platform)
    ──→ fallback to legacy columns if pivots empty
```

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `app/Services/IgdbClient.php` | Create | HTTP client: Twitch OAuth, rate limiter, Apicalypse builder |
| `app/Console/Commands/IgdbSyncCommand.php` | Create | `igdb:sync` Artisan command |
| `database/migrations/xxxx_add_normalized_tables.php` | Create | `genres`, `platforms`, `tags`, `companies` + 4 pivot tables |
| `database/migrations/xxxx_add_igdb_fields_to_games.php` | Create | Add `igdb_id`, `aggregated_rating`, `storyline`, `status`; nullable `genre`/`platforms` |
| `app/Models/Genre.php` | Create | BelongsToMany through `game_genre` |
| `app/Models/Platform.php` | Create | BelongsToMany through `game_platform` |
| `app/Models/Tag.php` | Create | BelongsToMany through `game_tag` |
| `app/Models/Company.php` | Create | HasMany through `game_company` with role |
| `app/Models/Game.php` | Modify | Add BelongsToMany relations for genres/platforms/tags/companies; rewrite scopes |
| `app/Models/Purchase.php` | Modify | Add `scopeMonthlySales` with driver-detect date truncation |
| `app/Http/Controllers/CatalogController.php` | Modify | Genre filter via `whereHas('genres')`, platform via `whereHas('platforms')`; remove hardcoded genre/platform lists |
| `app/Http/Controllers/AdminReportController.php` | Modify | `revenueByGenre` joins `game_genre`; monthly sales uses `scopeMonthlySales` |
| `app/Http/Controllers/HomeController.php` | Modify | Genre extraction from pivot via `Game::with('genres')->get()->pluck('genres.*.name')` |
| `database/seeders/GameSeeder.php` | Modify | Remove game data array; keep user/review/purchase seeding |
| `database/factories/GameFactory.php` | Modify | Use `afterCreating` to attach random genres/platforms from existing records |
| `.env` | Modify | Add `DB_CONNECTION=pgsql`, `IGDB_CLIENT_ID`, `IGDB_CLIENT_SECRET` |

## Interfaces / Contracts

```php
namespace App\Services;

class IgdbClient
{
    public function __construct(
        private CacheInterface $cache,
        private RateLimiterFactory $rateLimiter,
    ) {}

    /** @return array<array{id: int, name: string, slug: string}> */
    public function genres(): array;

    /** @return array<array{id: int, name: string, slug: string, abbreviation: ?string}> */
    public function platforms(): array;

    /** @return array<array{id: int, name: string, slug: string, country: ?int}> */
    public function companies(): array;

    /** Fetch games with optional limit. */
    public function games(int $limit = 500, int $offset = 0): array;
}
```

## Testing Strategy

| Layer | What to Test | Approach |
|-------|-------------|----------|
| Unit | `IgdbClient` token refresh, rate limiting | Mock Guzzle; assert correct headers, backoff |
| Unit | Sync command options (`--game-limit`, `--fresh`) | `Http::fake()` for IGDB + cached token |
| Integration | Pivot relationships (Game::genres, Game::platforms) | `RefreshDatabase` + factory assertions |
| Feature | Catalog filter by genre/platform via pivot | HTTP test with seeded pivot data |
| Feature | AdminReport revenueByGenre with pivot join | HTTP test, assert correct genre totals |
| Feature | Monthly sales with `DATE_TRUNC` (SQLite) | Already uses `strftime` fallback — assert no crash |

## Migration / Rollout

1. **Database**: Run migrations (new tables created, games extended, `genre`/`platforms` nullable)
2. **IGDB**: Run `php artisan igdb:sync` (populates normalized tables)
3. **Config**: Flip `.env` `DB_CONNECTION` to `pgsql`, port DB, run migrations on Postgres
4. **Seed**: `php artisan db:seed` (creates demo user + reviews/purchases only — no games)
5. **Verify**: Smoke-test catalog, admin reports, home page against live IGDB data

**Rollback**: Revert `.env` to `sqlite`, restore DB dump, `migrate:fresh --seed` with old seeder.

## Open Questions

- [ ] IGDB rate limit sharing — do we need a queue job for large fetches, or is single-command sync sufficient for the expected game count (~500)?
- [ ] Cover image handling — store IGDB URL directly or proxy through local storage?
