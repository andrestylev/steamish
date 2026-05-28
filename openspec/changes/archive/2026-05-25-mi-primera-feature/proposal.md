# Proposal: Mi Primera Feature — Digital Game Sales Platform

## Intent

Build a Steam-inspired digital game store from scratch — a Laravel 11 + Inertia.js + React SPA with PostgreSQL, Stripe sandbox payments, and Chart.js analytics. This is the project's first and only feature: the entire application.

## Scope

### In Scope
- Complete Steam-like UI: fixed header, hero carousel, scrollable game sections, Bootstrap 5 themed layout
- Full auth flow: register, login, profile, settings via Laravel Sanctum
- Game catalog with real-time search, multi-facet filters (genre/price/platform/rating), discount badges
- Game detail page: gallery, description, reviews with star ratings
- Cart + Stripe sandbox checkout + purchased-game library
- Wishlist (add/remove/view)
- Admin dashboard (top 10 sellers, revenue by genre, monthly sales) + user stats (hours played) — Chart.js
- Laravel seeders with sample data and a predefined user
- Steam-inspired color palette (#1b2838, #2a475e, #1a9fff, etc.)

### Out of Scope
- Multi-language i18n (header has a selector but no actual translations)
- Community features (forums, user profiles, friend lists, chat)
- Actual game files or DRM — store sells licenses/metadata only
- Mobile native apps (responsive web only)
- Admin game CRUD beyond seeders (manual DB seeding only)
- Real payment processing (Stripe sandbox only)

## Capabilities

> Contract between proposal and specs phases. Each new capability becomes `openspec/specs/<name>/spec.md`.

### New Capabilities
- `home-page`: Hero carousel, scrollable game sections, fixed header with search/nav, footer
- `user-auth`: Register, login, profile, account settings, Sanctum session management
- `game-catalog`: Real-time search, genre/price/platform/rating filters, discount tags
- `game-detail`: Image/video gallery, description, reviews with star ratings, action buttons
- `shopping-cart`: Cart CRUD, Stripe Checkout integration, purchase flow, user library
- `wishlist`: Add/remove games, view wishlist, move to cart
- `admin-reports`: Top 10 sellers, revenue by genre, monthly sales (Chart.js)
- `user-reports`: Most-played games by hours (Chart.js)
- `seed-data`: Predefined user + sample games/reviews/purchases via seeders
- `ui-theme`: Bootstrap 5 Steam palette, Motiva Sans typography

### Modified Capabilities
None — greenfield project, no existing specs.

## Approach

Laravel 11 MVC backend with Inertia.js rendering React pages. PostgreSQL for persistence, Sanctum for cookie-based sessions. Bootstrap 5 for layout with custom CSS variables for Steam palette. Chart.js via a React wrapper for analytics. Stripe Checkout (sandbox) for payments. Seeders for demo data. Monolith — no separate API layer.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `openspec/config.yaml` | New | SDD config with stack context and rules |
| Whole project | New | All source, routes, controllers, models, views, migrations, seeders |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Scope creep (wants all Steam features) | High | Explicit out-of-scope list; enforce at spec phase |
| Stripe sandbox → production complexity | Low | Sandbox only; no production Stripe keys stored |
| Inertia + React + Bootstrap integration quirks | Medium | Prototype a single page first before building out |
| Greenfield estimation inaccuracy | Medium | Build iteratively: auth → catalog → cart → reports |

## Rollback Plan

No production deployment exists. Rollback = reset branch: `git checkout master && git branch -D mi-primera-feature`. No data loss risk.

## Dependencies

- PHP 8.3, Composer, PostgreSQL, Node.js, npm (local dev environment)
- Stripe sandbox account + API keys
- Chart.js via npm

## Success Criteria

- [ ] Home page renders with hero carousel + 4 scrollable sections
- [ ] User can register, login, update profile
- [ ] Game catalog search returns results in <500ms
- [ ] Game detail page shows gallery, description, reviews, action buttons
- [ ] User can add to cart, checkout via Stripe sandbox, see game in library
- [ ] Admin dashboard shows Chart.js charts with seeded data
- [ ] User stats page shows hours played per game
