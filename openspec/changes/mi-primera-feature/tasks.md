# Tasks: Mi Primera Feature — Digital Game Sales Platform

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | 8,000–12,000 |
| 400-line budget risk | High |
| Chained PRs recommended | Yes |
| Delivery strategy | ask-always |
| Chain strategy | feature-branch-chain |

Decision needed before apply: Yes
Chained PRs recommended: Yes
Chain strategy: feature-branch-chain
400-line budget risk: High

### Suggested Work Units

| Unit | Goal | Base | Tests Incl. | Notes |
|------|------|------|-------------|-------|
| 1 | Migrations, Models, Config | `main` | Unit models | Zero deps |
| 2 | Auth + Layout | `main` or tracker | Auth feature tests | Depends on 1 |
| 3 | Home + Catalog | PR 2 branch | Catalog tests | Depends on 2 |
| 4 | Game Detail + Cart + Library | PR 3 branch | Cart + webhook tests | Depends on 3 |
| 5 | Wishlist + Reports | PR 4 branch | Wishlist + chart tests | Depends on 4 |
| 6 | Seeders + Polish | PR 5 branch | Seed validation | Depends on all |

## Phase 1: Foundation

- [x] 1.1 Create 6 migrations: `games`, `reviews`, `purchases`, `cart_items`, `wishlist_items`, `game_images`
- [x] 1.2 Create models: `Game`, `Review`, `Purchase`, `CartItem`, `WishlistItem`, `GameImage` in `app/Models/` with relations, casts, scopes
- [x] 1.3 Create `app/Services/StripeService.php` + `config/stripe.php`
- [x] 1.4 Install npm deps: Bootstrap 5, `react-bootstrap`, Chart.js, `react-chartjs-2`, Stripe JS

## Phase 2: Auth & Layout

- [x] 2.1 Create `routes/auth.php`, Sanctum config, `app/Http/Controllers/ProfileController.php`
- [x] 2.2 Create `resources/js/Layouts/AuthenticatedLayout.jsx`, `GuestLayout.jsx`
- [x] 2.3 Create `resources/js/Components/Header.jsx` (logo, search, nav, cart badge), `Footer.jsx`
- [x] 2.4 Create `resources/sass/app.scss` with Bootstrap import + Steam palette CSS vars (`#1b2838`, `#2a475e`, `#1a9fff`)

## Phase 3: Home & Catalog

- [ ] 3.1 Create `HomeController` + `resources/js/Pages/Home.jsx` + `HeroCarousel.jsx` (5 featured games, cycling)
- [ ] 3.2 Create `resources/js/Components/GameCard.jsx` (cover, price, discount strikethrough + badge)
- [ ] 3.3 Create `CatalogController` + `resources/js/Pages/Catalog.jsx` + `SearchBar.jsx` + `FilterSidebar.jsx` (genre/price/platform/rating)

## Phase 4: Game Detail, Cart & Checkout

- [ ] 4.1 Create `GameController` + `resources/js/Pages/GameDetail.jsx` with gallery, desc, system reqs, price, reviews
- [ ] 4.2 Create `resources/js/Components/ReviewCard.jsx` + `StarRating.jsx` (1–5, only purchasers may review)
- [ ] 4.3 Create `CartController` + `resources/js/Pages/Cart.jsx` + `CheckoutController` + `Checkout.jsx` (Stripe Checkout redirect)
- [ ] 4.4 Create `StripeWebhookController` — signature verify via `Webhook::constructEvent()`, `Purchase::create()`, clear cart
- [ ] 4.5 Create `LibraryController` + `resources/js/Pages/Library.jsx`

## Phase 5: Wishlist & Reports

- [ ] 5.1 Create `WishlistController` + `resources/js/Pages/Wishlist.jsx` (add/remove, move to cart)
- [ ] 5.2 Create `AdminReportController` + `resources/js/Pages/Admin/Reports.jsx` + `ChartWidget.jsx` (3 Chart.js charts: top 10 bar, revenue-by-genre pie, monthly line)
- [ ] 5.3 Create `UserReportController` + `resources/js/Pages/User/Stats.jsx` (top 5 played horizontal bar)

## Phase 6: Seeders & Polish

- [ ] 6.1 Create `database/seeders/GameSeeder.php`: demo user `demo@steamish.test`/`password`, 20+ games (4 genres), 50+ reviews, 10+ purchases with playtime
- [ ] 6.2 Update `database/seeders/DatabaseSeeder.php` to call `GameSeeder`
- [ ] 6.3 Complete `routes/web.php` with all route definitions from design route map
- [ ] 6.4 Responsive verification: 320px viewport no overflow, tappable targets

## Phase 7: Testing (PHPUnit)

- [ ] 7.1 Model tests: relations, `price:decimal` casts, `discounted`/`by_genre`/`search` scopes
- [ ] 7.2 Auth tests: register, login, profile update, wrong password rejection
- [ ] 7.3 Catalog tests: search by name, genre/price/platform filter combo, empty results
- [ ] 7.4 Cart + Webhook tests: add/remove items, guest redirect, valid+invalid Stripe sig, purchase fulfillment
- [ ] 7.5 Wishlist + Reports tests: toggle, list, remove; admin chart data query, empty states
