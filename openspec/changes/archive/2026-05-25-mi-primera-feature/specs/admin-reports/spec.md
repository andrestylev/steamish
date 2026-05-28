# Admin Reports

### Requirement: Dashboard Charts
Three Chart.js charts (bar top 10, pie revenue by genre, line monthly sales). Zero data shows empty messages.

| # | Given | When | Then |
|---|-------|------|------|
| 1 | admin at `/admin/reports` with purchases | loads | three charts, top 10 sellers |
| 2 | admin no purchases | loads | empty-state messages |
