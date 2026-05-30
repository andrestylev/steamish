# Admin Reports

### Requirement: Dashboard Charts

Three Chart.js charts (bar top 10, pie revenue by genre, line monthly sales). All date-truncation queries MUST use PostgreSQL's `DATE_TRUNC` instead of SQLite's `strftime`. Revenue-by-genre MUST join through `game_genre` pivot instead of the deprecated `genre` column. Zero data shows empty messages.
(Previously: date queries used `strftime('%Y-%m', purchases.created_at)`; revenue-by-genre used direct `games.genre` column)

| # | Given | When | Then |
|---|-------|------|------|
| 1 | admin at `/admin/reports` with purchases | loads | three charts render with PostgreSQL-compatible queries, top 10 sellers via pivot joins |
| 2 | admin no purchases | loads | empty-state messages shown |
| 3 | `strftime` call detected in query | `AdminReportController` loads | query uses `DATE_TRUNC('month', purchases.created_at)` instead |
| 4 | revenue-by-genre chart | loads | join goes through `game_genre` pivot table, not `games.genre` |
