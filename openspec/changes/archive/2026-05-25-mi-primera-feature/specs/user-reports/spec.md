# User Reports

### Requirement: Playtime Chart
Horizontal bar of top 5 played games (hours) on `/my/stats`. No playtime shows empty message.

| # | Given | When | Then |
|---|-------|------|------|
| 1 | user with playtime | visits `/my/stats` | chart top 5 by hours |
| 2 | user no playtime | visits `/my/stats` | empty message |
