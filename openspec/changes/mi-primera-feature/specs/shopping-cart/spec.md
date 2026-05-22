# Shopping Cart

### Requirement: Purchase Flow
Auth users add/remove items, see subtotal, Stripe sandbox checkout. Success adds to library, empties cart. Failure keeps items, shows error.

| # | Given | When | Then |
|---|-------|------|------|
| 1 | auth user cart items | Stripe succeeds | games in library, cart empty |
| 2 | in Stripe Checkout | payment cancelled | back to cart, items remain |
