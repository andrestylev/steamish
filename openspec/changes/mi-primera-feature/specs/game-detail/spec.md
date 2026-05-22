# Game Detail

### Requirement: Detail Page
Gallery, description, system reqs, price, reviews (1-5 stars). Auth purchasers may review. Add to Cart and Wishlist buttons.

| # | Given | When | Then |
|---|-------|------|------|
| 1 | user visits `/games/{slug}` | loads | gallery, desc, price, reviews, action buttons |
| 2 | auth purchaser | submits 1-5 star review | review appears, avg updates |
