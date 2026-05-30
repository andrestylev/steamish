# Delta for Seed Data

## MODIFIED Requirements

### Requirement: Seeder

`db:seed` creates the demo user (`demo@steamish.test`/`password`) and sample reviews and purchases. Games are no longer seeded — `php artisan igdb:sync` provides game data. The demo user MUST still be created with their review and purchase history.
(Previously: `db:seed` created 20+ hardcoded games across 4 genres, 50+ reviews, and 10+ purchases with playtime)

| # | Given | When | Then |
|---|-------|------|------|
| 1 | fresh DB migrated | `db:seed` runs | demo user, reviews, and purchases exist; no games table populated |
| 2 | fresh DB migrated | `igdb:sync` then `db:seed` | games populated by sync, demo user with reviews/purchases by seeder |

## REMOVED Requirements

None. The Seeder requirement is modified, not removed.
