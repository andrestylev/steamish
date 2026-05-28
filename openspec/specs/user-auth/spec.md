# User Auth

### Requirement: Auth Flow
Register (email + pw 8+ chars), login via Sanctum, profile (avatar, name, bio), settings (change pw, email). Bad login returns error, no session.

| # | Given | When | Then |
|---|-------|------|------|
| 1 | visitor valid creds | registers | account created, logged in |
| 2 | registered user | wrong password | login fails |
