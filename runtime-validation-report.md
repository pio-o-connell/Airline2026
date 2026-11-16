# Runtime Validation Report

**Generated:** 2026-11-16T14:36:41Z
**Target:** Airline2026

## Summary

| Step | Status | Exit code | Details |
|---|---|---:|---|
| Startup | PASS | n/a | PHP 8.4.26 development server started by Playwright |
| Existing baseline | PASS | 0 | 26 tests passed |
| Full regression suite | PASS | 0 | 39 passed, 2 skipped |

**Overall:** PASS for the available local browser and database environment.

## Environment

| Capability | Result |
|---|---|
| Node.js | Available — 22.20.0 |
| npm | Available — 10.9.3 |
| Playwright | Available — 1.65.1 |
| PHP | Available — 8.4.26 |
| Docker | Unavailable — `docker` is not installed |
| Browser tier | Playwright |
| Infrastructure tier | Existing local MySQL database |

## Test evidence

The canonical command was used:

```powershell
npm test
```

The command executed 41 tests with one worker and exited with code `0`.
The result was 39 passed and 2 skipped.

Category inventory checks also passed with the following discovered counts:

| Category | Tests |
|---|---:|
| `@SMK` | 5 |
| `@FUN` | 38 |
| `@INT` | 14 |
| `@REG` | 38 |
| `@SEC` | 14 |
| `@RSP` | 3 |
| `@E2E` | 32 |

Added runnable coverage:

- `tests/security/access-control.spec.js`
- `tests/journeys/customer-booking.spec.js`
- `tests/integration/payment-boundaries.spec.js`

## Skipped coverage

1. Suspended-account rejection is skipped because the current authentication
   implementation does not enforce the `user.suspend` flag.
2. Successful Stripe payment and booking persistence are skipped because they
   require Stripe test credentials and a disposable database.

## Known gaps

- Route creation, editing and deletion were not run against the working
  database.
- Successful Stripe payment, booking persistence and duplicate-confirmation
  coverage require isolated infrastructure.
- Tablet viewport coverage at 768px is not yet implemented.
- AC220 database and renderer seat grouping remain inconsistent.
