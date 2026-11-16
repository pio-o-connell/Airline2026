# Airline2026 Test Catalogue

This catalogue audits the existing Playwright suite against the testing strategy
provided for Airline2026. Tests use the real PHP server and seeded MySQL records.

## Execution baseline

| Item | Result |
|---|---|
| Canonical command | `npm test` |
| Existing baseline | 26 passed |
| Browser tier | Playwright 1.65.1 |
| Runtime | Node 22.20.0, PHP 8.4.26 |
| Docker tier | Unavailable on this workstation |
| Database safety | Ordinary tests do not import, drop or recreate the working database |

## Existing coverage map

| IDs | File | Coverage |
|---|---|---|
| TC-001–TC-007, TC-011 | `tests/pages/public-pages.spec.js` | Guest homepage, navigation, search, flight details and Cart retention |
| TC-008–TC-022 | `tests/pages/booking-workflow.spec.js` | Multiple flights, Cart totals/removal, seats, passenger details and checkout |
| TC-023–TC-024 | `tests/pages/authentication.spec.js` | Valid and invalid login |
| Unnumbered registration test | `tests/pages/authentication.spec.js` | Registration form fields |
| TC-031–TC-033, TC-035 | `tests/pages/administrator-pages.spec.js` | Administrator login, access control, route editor and manifest |
| TC-036–TC-038 | `tests/responsive/mobile.spec.js` | 390px and 320px booking flows |

## Added coverage

| IDs | File | Coverage |
|---|---|---|
| TC-041–TC-045 | `tests/security/access-control.spec.js` | Direct administrator access, customer access denial, invalid credentials and suspended-account gap |
| TC-046–TC-048 | `tests/journeys/customer-booking.spec.js` | Registered customer journey, checkout persistence across reload, validation and logout |
| TC-049–TC-051 | `tests/integration/payment-boundaries.spec.js` | Invalid Stripe-session handling, payment cancellation retention and isolated payment-persistence requirement |

## Gaps and status

| Area | Status | Notes |
|---|---|---|
| Guest journey before authentication | Covered | Existing functional tests |
| Registered customer journey | Partially covered | Added through checkout; successful payment remains environment-dependent |
| Administrator read-only journey | Covered | Existing tests plus direct access checks |
| Route creation/edit/delete persistence | Not run | Requires disposable database to avoid changing working data |
| Suspended-account enforcement | Not implemented | `authenticateUser()` does not currently enforce `user.suspend`; TC-045 is skipped |
| Stripe successful payment | Not run | Requires Stripe test key, Stripe test payment, and disposable database |
| Stripe cancelled/invalid payment | Covered | Added TC-049 and TC-050 |
| Booking persistence and duplicate confirmation | Not run | Requires isolated database and Stripe test session |
| Tablet viewport | Gap | Current responsive suite covers 390px and 320px, not 768px |
| AC220 seat layout | Known defect | Database uses A–F rows while renderer uses a different grouping |

## Test-data policy

Read-only tests use existing seeded accounts and flights. Registration, route
mutation, booking persistence, seat allocation, and payment persistence must be
run only when the test environment explicitly points at a disposable database.
No new users, password changes, bcrypt migration, or schema changes are part of
this coverage work.
