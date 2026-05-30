# Delta for Game Catalog

## MODIFIED Requirements

### Requirement: Browsing

Real-time name search, filters (genre/price/platform/rating). Genre and platform filters MUST use `whereHas` through pivot tables (`game_genre`, `game_platform`) instead of the deprecated `genre` / `platforms` columns. Discount badge and strikethrough price on sale. Zero results show "No games found."
(Previously: genre and platform filters used direct `where('genre')` and `whereJsonContains('platforms')` column checks)

| # | Given | When | Then |
|---|-------|------|------|
| 1 | user on catalog | search + PC/$10-30 filter | matching games show via pivot join, discounts badge rendered |
| 2 | zero results | renders | "No games found" message |
| 3 | user filters by genre "RPG" | catalog loads | only games linked via `game_genre` to that genre shown |
| 4 | legacy `genre` column contains data but pivots empty | filter applied | fallback to legacy column, return empty results (expected post-migration gap) |

## REMOVED Requirements

None. The Browsing requirement is modified, not removed.
