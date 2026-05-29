# Tasks: IGDB + PostgreSQL Integration

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | 600–900 |
| User review budget | 800 lines (D2) |
| 400-line budget risk | High |
| Chained PRs recommended | Yes |
| Suggested split | PR 1: Foundation → PR 2: Core → PR 3: Polish |
| Delivery strategy | ask-on-risk |
| Chain strategy | pending |

Decision needed before apply: Yes
Chained PRs recommended: Yes
Chain strategy: feature-branch-chain
400-line budget risk: High

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | Migrations + Models | PR 1 | 8 new tables, Game/Purchase updates, 4 new models; ~250 lines |
| 2 | IGDB Client + Sync + Query rewrites | PR 2 | IgdbClient, sync command, 3 controller rewrites; ~300 lines |
| 3 | Factory + Seeder + Testing | PR 3 | GameFactory, GameSeeder, .env, all test scenarios; ~200 lines |

## Phase 1: Foundation

- [x] 1.1 Create migration `xxxx_add_normalized_tables` — genres, platforms, tags, companies + 4 pivots with cascade delete
- [x] 1.2 Create migration `xxxx_add_igdb_fields_to_games` — igdb_id (unique), aggregated_rating, storyline, status; nullable genre/platforms
- [x] 1.3 Create `app/Models/{Genre,Platform,Tag,Company}.php` — BelongsToMany with pivot table; Company pivot has role enum
- [x] 1.4 Modify `app/Models/Game.php` — add BelongsToMany relations; rewrite scopes with pivot-first, legacy-fallback
- [x] 1.5 Modify `app/Models/Purchase.php` — add scopeMonthlySales with driver-detect DATE_TRUNC/strftime

## Phase 2: IGDB Integration

- [ ] 2.1 Create `app/Services/IgdbClient.php` — Twitch OAuth (cache 60d, 401 refresh), RateLimiter 4 req/s, Apicalypse POST builder; genres(), platforms(), companies(), games() methods
- [ ] 2.2 Create `app/Console/Commands/IgdbSyncCommand.php` — igdb:sync with step methods in entity dependency order, --game-limit=N, --fresh, upsert on igdb_id

## Phase 3: Query Rewrites

- [ ] 3.1 Modify `CatalogController.php` — genre filter via whereHas('genres'), platform via whereHas('platforms'); remove hardcoded arrays
- [ ] 3.2 Modify `AdminReportController.php` — revenueByGenre joins game_genre pivot; monthly sales uses scopeMonthlySales
- [ ] 3.3 Modify `HomeController.php` — genre extraction via Game::with('genres')->pluck pivot path

## Phase 4: Factory + Seeder + Config

- [ ] 4.1 Modify `GameFactory.php` — afterCreating callback to attach random Genre/Platform from existing records
- [ ] 4.2 Modify `GameSeeder.php` — remove hardcoded game array; keep demo user, reviews, purchases
- [ ] 4.3 Modify `.env.example` — add DB_CONNECTION=pgsql, IGDB_CLIENT_ID, IGDB_CLIENT_SECRET

## Phase 5: Testing

- [ ] 5.1 Unit: IgdbClient token refresh, rate-limit enforcement, 429 backoff, missing-cred abort (mock Guzzle)
- [ ] 5.2 Unit: IgdbSyncCommand --game-limit, --fresh, step skipping, progress output (Http::fake)
- [ ] 5.3 Integration: pivot relationships (Game::genres, ->platforms, ->tags, ->companies) via RefreshDatabase
- [ ] 5.4 Feature: catalog filter returns correct results via whereHas on pivots
- [ ] 5.5 Feature: admin report revenueByGenre joins pivot; monthly sales works on both drivers; empty-state renders
- [ ] 5.6 Feature: seeder runs without games; igdb:sync → seeder creates user + purchases
