# Design: Mi Primera Feature — Digital Game Sales Platform

## Technical Approach

Monolith Laravel 11 MVC + Inertia.js rendering React pages. Sanctum cookie-session auth. PostgreSQL persistence. Bootstrap 5 with CSS variables for Steam palette. Stripe Checkout webhook-based payment flow. Chart.js via react-chartjs-2 for analytics. Build order per proposal risk mitigation: auth → catalog → game detail → cart/checkout → wishlist → reports → seeders → theme polish.

## Architecture Decisions

| Decision | Choice | Alternatives | Rationale |
|----------|--------|-------------|-----------|
| Pattern | Laravel MVC + Inertia SPA | Separate API + SPA | Monolith = no CORS, single auth layer, simpler deploy |
| Auth | Sanctum cookie sessions | JWT, Breeze/Fortify | Sanctum ships Inertia support natively; cookies avoid token management |
| Payments | Stripe Checkout + webhooks | Stripe Elements, direct API | Checkout handles billing UI; webhooks ensure idempotent fulfillment |
| Charts | react-chartjs-2 | Laravel charts lib, raw Chart.js | Reuses React context; wrapper handles mount/unmount lifecycle |
| Styling | Bootstrap 5 + CSS vars | Tailwind, custom CSS | Proposal mandates Bootstrap; vars swap palette without rebuild |
| DB queries | Eloquent + eager loading | Raw SQL, query builder | Simple graph of relationships; eager loading prevents catalog N+1 |
| Auth scaffolding | Manual (no Breeze/Jetstream) | Laravel Breeze, Jetstream | Full control over auth pages as Inertia React components; no unused Livewire/Tall stacks |

## Data Flow

```
Browser ──Inertia──→ Laravel Route ──→ Controller ──→ Eloquent ──→ PostgreSQL
                        │                              │
                        ↓                              ↓
                   Inertia::render() ←── React page ←── $page.props

Payment flow (Stripe):
  Browser ──→ Checkout page ──→ Stripe Checkout ──→ Webhook ──→ StripeWebhookController
                                                                      │
                                                                 Purchase::create()
                                                                 CartItem::where('game_id')->delete()
```

## Database Schema

| Table | Key Columns | Relations |
|-------|-------------|-----------|
| `users` | id, name, username, email, password, avatar, bio, timezone | HasMany reviews, purchases, cart_items, wishlist_items |
| `games` | id, title, slug, text fields, price, discount_price, discount_pct, is_discounted, release_date, developer, publisher, genre, platforms, cover, header, rating_avg, rating_count, min_req, rec_req | HasMany reviews, purchases, cart_items, wishlist_items, game_images |
| `reviews` | id, user_id, game_id, rating (smallint 1-5), body, hours_played, is_recommended | BelongsTo user/game. Unique(user_id, game_id) |
| `purchases` | id, user_id, game_id, stripe_session_id (unique), amount_paid | BelongsTo user/game |
| `cart_items` | id, user_id, game_id | BelongsTo user/game. Unique(user_id, game_id) |
| `wishlist_items` | id, user_id, game_id | BelongsTo user/game. Unique(user_id, game_id) |
| `game_images` | id, game_id, url, type (screenshot/gallery/background), sort_order | BelongsTo game |

## File Changes

All files are **Create** — greenfield project.

| Path | Purpose |
|------|---------|
| `app/Models/{Game,Review,Purchase,CartItem,WishlistItem,GameImage}.php` | Eloquent models with relationships, casts (`price:decimal`), and scopes (discounted, by_genre, search) |
| `app/Http/Controllers/{Home,Catalog,Game,Cart,Checkout,Wishlist,Library,Profile,AdminReport,UserReport}Controller.php` | Page controllers returning `Inertia::render()` |
| `app/Http/Controllers/StripeWebhookController.php` | Stripe webhook — verifies sig, creates Purchase, clears cart |
| `app/Services/StripeService.php` | `createCheckoutSession()`, `verifyWebhookSignature()` |
| `database/migrations/` x6 | Tables for games, reviews, purchases, cart_items, wishlist_items, game_images |
| `database/seeders/{DatabaseSeeder,GameSeeder}.php` | Admin user + 20 sample games + reviews + purchases |
| `resources/js/Pages/{Home,Catalog,GameDetail,Cart,Checkout,Library,Wishlist,Profile,Admin/Reports,User/Stats}.jsx` | Inertia page components |
| `resources/js/Components/{Header,Footer,HeroCarousel,GameCard,ReviewCard,StarRating,SearchBar,FilterSidebar,CartBadge,ChartWidget}.jsx` | Shared React components |
| `resources/js/Layouts/{Authenticated,Guest}Layout.jsx` | Page layouts with header/footer |
| `routes/web.php` | All Inertia GET/POST routes |
| `routes/auth.php` | Login, register, logout, profile |
| `resources/sass/app.scss` | Bootstrap import + CSS custom properties for Steam palette |
| `config/stripe.php` | Stripe key + webhook secret config |

## Route Map

| Method | URI | Controller@method | Page |
|--------|-----|-------------------|------|
| GET | `/` | HomeController@index | Home |
| GET | `/catalog` | CatalogController@index | Catalog |
| GET | `/games/{game:slug}` | GameController@show | GameDetail |
| POST | `/cart/add/{game}` | CartController@add | Redirect |
| DELETE | `/cart/{item}` | CartController@remove | Redirect |
| GET | `/cart` | CartController@index | Cart |
| POST | `/wishlist/{game}` | WishlistController@toggle | Redirect |
| GET | `/checkout` | CheckoutController@index | Checkout |
| POST | `/stripe/webhook` | StripeWebhookController (no CSRF) | — |
| GET | `/library` | LibraryController@index | Library |
| GET | `/admin/reports` | AdminReportController@index | Admin/Reports |
| GET|PUT | `/profile` | ProfileController@{edit,update} | Profile |
| GET | `/user/stats` | UserReportController@index | User/Stats |

## Stripe Integration

```
User clicks "Buy on cart/checkout"
  → StripeService::createCheckoutSession($cartItems, $user)
  → Returns Stripe Checkout URL
  → User completes payment on Stripe
  → Stripe POSTs checkout.session.completed to /stripe/webhook
  → StripeWebhookController verifies signature (config/stripe.php)
  → Purchase::create() for each item (idempotent via stripe_session_id unique)
  → CartItem::whereGameId()->delete()
```

All webhook logic uses `Stripe\Webhook::constructEvent()` for signature verification. No redirects in webhook.

## Testing Strategy

| Layer | Focus | Approach |
|-------|-------|----------|
| Unit | Models (relations, casts, scopes) | PHPUnit with factories |
| Unit | StripeService | Mock Stripe\Checkout\Session |
| Feature | Controllers (page render, auth gates) | `actingAs()` + Inertia assertions |
| Feature | Cart/wishlist mutations, purchase flow | HTTP tests with DB assertions |
| Feature | Stripe webhook | `POST /stripe/webhook` with test payload + signature |
| JS | Component render (manual in this phase) | Visual validation via `php artisan serve` |

## Open Questions

- [ ] Stripe webhook signing secret — must be env-configurable via `STRIPE_WEBHOOK_SECRET`
