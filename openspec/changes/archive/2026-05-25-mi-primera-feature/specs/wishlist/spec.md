# Wishlist

### Requirement: Wishlist
Auth users add/remove from detail or catalog. `/wishlist` shows saved games with cover, price, Move to Cart. Removals update without reload.

| # | Given | When | Then |
|---|-------|------|------|
| 1 | auth user game detail | clicks Add to Wishlist | appears in wishlist |
| 2 | game in wishlist | clicks Remove | removed, list updates |
