# Seed Data

### Requirement: Seeder
`db:seed` creates: demo user (`demo@steamish.test`/`password`), 20+ games (4 genres), 50+ reviews, 10+ purchases with playtime.

| # | Given | When | Then |
|---|-------|------|------|
| 1 | fresh DB migrated | `db:seed` runs | demo user, games, reviews, purchases exist |
