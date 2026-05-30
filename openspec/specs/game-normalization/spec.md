# Game Normalization

## Purpose

Normalize the games schema with dedicated tables for genres, platforms, tags, and companies, linked through pivot tables. Deprecate denormalized `genre` / `platforms` columns while keeping them nullable for rollback.

## Requirements

### Requirement: Schema Structure

The database MUST contain these new tables with the following roles:

| Table | Columns | Purpose |
|-------|---------|---------|
| `genres` | `id`, `igdb_id` (unique), `name`, `slug`, `timestamps` | Game genres |
| `platforms` | `id`, `igdb_id` (unique), `name`, `slug`, `abbreviation`, `timestamps` | Game platforms |
| `tags` | `id`, `igdb_id` (unique), `name`, `slug`, `timestamps` | IGDB tags |
| `companies` | `id`, `igdb_id` (unique), `name`, `slug`, `country`, `timestamps` | Game companies |
| `game_genre` | `game_id`, `genre_id` | Genre pivot |
| `game_platform` | `game_id`, `platform_id` | Platform pivot |
| `game_tag` | `game_id`, `tag_id` | Tag pivot |
| `game_company` | `game_id`, `company_id`, `role` (enum: developer/publisher) | Company pivot with role |

All pivot tables MUST cascade on delete.

### Requirement: Games Table Migration

The `games` table MUST add these columns, all nullable:

| Column | Type | Purpose |
|--------|------|---------|
| `igdb_id` | bigint, unique | IGDB primary key |
| `aggregated_rating` | decimal(4,1) | Critic aggregate score |
| `storyline` | text | Extended plot description |
| `status` | string, nullable | IGDB game status (released/alpha/beta/etc) |

The existing `genre` and `platforms` columns MUST be made nullable but NOT dropped — kept for rollback compatibility.

| # | Given | When | Then |
|---|-------|------|------|
| 1 | games table has data | migration runs | igdb_id/aggregated_rating/storyline/status added, genre/platforms nullable |
| 2 | rollback needed | `migrate:rollback` | new columns removed, genre/platforms restored to NOT NULL |

### Requirement: Rollback Safety

All migrations MUST be reversible. The sync command MUST NOT fail if `genre`/`platforms` columns still contain legacy data. New code MUST write to pivots only; reads MAY fall back to legacy columns if pivots are empty.

| # | Given | When | Then |
|---|-------|------|------|
| 1 | legacy genre column has data | game browsed | catalog uses pivot if populated, falls back to legacy column |
| 2 | migration rolled back | app runs | pivots dropped, genre/platforms columns functional |
