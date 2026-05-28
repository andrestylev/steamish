# Game Catalog

### Requirement: Browsing
Real-time name search, filters (genre/price/platform/rating). Discount badge and strikethrough price on sale. Zero results show "No games found."

| # | Given | When | Then |
|---|-------|------|------|
| 1 | user on catalog | search + PC/$10-30 filter | matching games show, discounts badge |
| 2 | zero results | renders | "No games found" message |
