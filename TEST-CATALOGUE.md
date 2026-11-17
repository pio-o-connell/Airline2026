# Airline2026 Test Catalogue

This is the central register for the automated Playwright suite. Category tags
are assigned per test and are not inferred from the containing directory.

## Current inventory

| ID | Test | Categories | Role | Objective | Preconditions | Expected | Status | Automation | Source |
|---|---|---|---|---|---|---|---|---|---|
| TC-001 | Public homepage loads with search workspace | SMK, FUN, REG, E2E | Guest | Verify the public entry point and search controls | PHP server and database available | Homepage and search fields are visible | Pass | Automated | `tests/pages/public-pages.spec.js` |
| TC-002 | Guest navigation exposes booking destinations | FUN, REG, E2E | Guest | Verify guest navigation state | Guest session | Home and login links work; Cart and Checkout are disabled | Pass | Automated | `tests/pages/public-pages.spec.js` |
| TC-003 | Valid route/date search displays flights | SMK, FUN, INT, REG, E2E | Guest | Verify search-to-results integration | Seeded route and flight data | Matching flight results appear | Pass | Automated | `tests/pages/public-pages.spec.js` |
| TC-004 | Passenger count rejects zero | FUN, REG | Guest | Verify lower passenger bound | Homepage loaded | Browser rejects zero | Pass | Automated | `tests/pages/public-pages.spec.js` |
| TC-005 | Passenger count rejects values above twenty | FUN, REG | Guest | Verify upper passenger bound | Homepage loaded | Browser rejects 21 | Pass | Automated | `tests/pages/public-pages.spec.js` |
| TC-006 | Selecting a flight displays details and fare | FUN, REG, E2E | Guest | Verify flight selection presentation | Search results available | Details and fare are shown | Pass | Automated | `tests/pages/public-pages.spec.js` |
| TC-007 | Selected flight remains in guest Cart | SMK, FUN, INT, REG, E2E | Guest | Verify adding a flight to Cart | Searchable seeded flight | Cart contains the selected flight | Pass | Automated | `tests/pages/public-pages.spec.js` |
| TC-008 | Adding a second flight retains both flights | FUN, INT, REG, E2E | Guest | Verify multi-flight Cart state | Two searchable seeded flights | Cart contains two independent flights | Pass | Automated | `tests/pages/booking-workflow.spec.js` |
| TC-009 | Cart displays quantity, fare and total | FUN, REG, E2E | Guest | Verify Cart calculations and display | Flight added with two passengers | Quantity and EUR totals are shown | Pass | Automated | `tests/pages/booking-workflow.spec.js` |
| TC-010 | Removing a flight empties the Cart | FUN, REG, E2E | Guest | Verify Cart removal and navigation state | Flight in Cart | Cart is empty and disabled in navigation | Pass | Automated | `tests/pages/booking-workflow.spec.js` |
| TC-011 | Guest Cart survives page reload | FUN, INT, REG, E2E | Guest | Verify session-backed Cart retention | Flight in guest Cart | Cart still contains the flight after reload | Pass | Automated | `tests/pages/public-pages.spec.js` |
| TC-012 | Seat selection displays aircraft map | FUN, INT, REG, E2E | Guest | Verify aircraft-specific map rendering | Flight in Cart | Seat map and aircraft code are visible | Pass | Automated | `tests/pages/booking-workflow.spec.js` |
| TC-013 | Seat selection saves a selected seat | FUN, INT, REG, E2E | Guest | Verify available seat selection | Seat map loaded | Selected seat enables the save action | Pass | Automated | `tests/pages/booking-workflow.spec.js` |
| TC-014 | Seat selection limits seats to passengers | FUN, SEC, REG, E2E | Guest | Verify client-side selection limit | One-passenger Cart and seat map | Additional seat cannot be selected | Pass | Automated | `tests/pages/booking-workflow.spec.js` |
| TC-015 | Occupied seats are disabled | FUN, INT, SEC, REG, E2E | Guest | Verify database occupancy reaches the UI | Seeded occupied flight | Occupied seats are disabled | Pass | Automated | `tests/pages/booking-workflow.spec.js` |
| TC-016 | Multiple-flight seat selections stay independent | FUN, INT, REG, E2E | Guest | Verify seat state is keyed by flight | Two-flight Cart | Each map retains its own selection | Pass | Automated | `tests/pages/booking-workflow.spec.js` |
| TC-018 | Passenger details precede guest authentication | SMK, FUN, INT, REG, E2E | Guest | Verify passenger form occurs before auth | Flight and seat selected | Checkout form accepts passenger details | Pass | Automated | `tests/pages/booking-workflow.spec.js` |
| TC-020 | Incomplete passenger details prevent continuation | FUN, REG, E2E | Guest | Verify required checkout fields | Checkout form loaded | Continue action remains disabled | Pass | Automated | `tests/pages/booking-workflow.spec.js` |
| TC-021 | Checkout summary shows booking details | FUN, INT, REG, E2E | Guest | Verify passenger, seat and fare summary | Complete passenger form and selected seat | Summary and EUR total are correct | Pass | Automated | `tests/pages/booking-workflow.spec.js` |
| TC-022 | Guest checkout offers login and registration | FUN, SEC, REG, E2E | Guest | Verify authentication gate before payment | Complete guest checkout form | Login and registration actions appear | Pass | Automated | `tests/pages/booking-workflow.spec.js` |
| TC-023 | Valid customer credentials authenticate | SMK, FUN, SEC, REG, E2E | Customer | Verify customer login | Seeded customer account | Customer navigation is visible | Pass | Automated | `tests/pages/authentication.spec.js` |
| TC-024 | Invalid credentials are rejected | FUN, SEC, REG | Guest | Verify authentication failure | Login page loaded | User remains Guest | Pass | Automated | `tests/pages/authentication.spec.js` |
| TC-025 | Registration form exposes required fields | FUN, REG | Guest | Verify registration input contract | Registration page loaded | Required account fields are visible | Pass | Automated | `tests/pages/authentication.spec.js` |
| TC-031 | Administrator login opens route management | SMK, FUN, SEC, REG, E2E | Administrator | Verify administrator authentication | Seeded `rmiller` account | Route management is visible | Pass | Automated | `tests/pages/administrator-pages.spec.js` |
| TC-032 | Administrator dashboard requires authentication | FUN, SEC, REG | Guest | Verify direct admin access restriction | Guest session | Login page is returned | Pass | Automated | `tests/pages/administrator-pages.spec.js` |
| TC-033 | Administrator can open route editor | FUN, REG, E2E | Administrator | Verify protected route editor navigation | Administrator session | Route editor is displayed | Pass | Automated | `tests/pages/administrator-pages.spec.js` |
| TC-035 | Administrator can open flight manifest | SMK, FUN, REG, E2E | Administrator | Verify protected manifest access | Administrator session | Manifest page is displayed | Pass | Automated | `tests/pages/administrator-pages.spec.js` |
| TC-036 | Homepage remains usable at 390px | FUN, REG, RSP, E2E | Guest | Verify mobile homepage layout | Browser viewport 390px | No horizontal overflow | Pass | Automated | `tests/responsive/mobile.spec.js` |
| TC-037 | Booking and seats usable at 390px | FUN, REG, RSP, E2E | Guest | Verify mobile booking controls | Browser viewport 390px | Seat selection reaches checkout | Pass | Automated | `tests/responsive/mobile.spec.js` |
| TC-038 | Booking controls usable at 320px | FUN, REG, RSP, E2E | Guest | Verify narrow mobile controls | Browser viewport 320px | Seat selection reaches checkout | Pass | Automated | `tests/responsive/mobile.spec.js` |
| TC-041 | Unauthenticated users cannot open admin dashboard | SEC, FUN, REG | Guest | Verify direct URL authorization | Guest session | Login page is returned | Pass | Automated | `tests/security/access-control.spec.js` |
| TC-042 | Customers cannot open admin routes | SEC, FUN, REG | Customer | Verify customer/admin separation | Seeded customer session | Login page is returned | Pass | Automated | `tests/security/access-control.spec.js` |
| TC-043 | Administrator authentication grants route access | SEC, FUN, REG, E2E | Administrator | Verify authorized admin access | Seeded `rmiller` account | Add-route form is displayed | Pass | Automated | `tests/security/access-control.spec.js` |
| TC-044 | Invalid credentials do not authenticate | SEC, FUN, REG | Guest | Verify invalid session cannot access admin | Invalid login submitted | Guest remains denied | Pass | Automated | `tests/security/access-control.spec.js` |
| TC-045 | Suspended account is rejected | SEC, FUN, REG | Customer | Verify suspended-account enforcement | Suspended seeded account | Authentication is rejected | Skipped | Automated | `tests/security/access-control.spec.js` |
| TC-046 | Customer retains checkout state | FUN, INT, REG, E2E | Customer | Verify authenticated customer booking flow | Seeded customer and flights | Cart, seats, passenger data and totals survive reload | Pass | Automated | `tests/journeys/customer-booking.spec.js` |
| TC-047 | Incomplete customer details block payment | FUN, REG, E2E | Customer | Verify customer checkout validation | Customer seat selection complete | Continue action remains disabled | Pass | Automated | `tests/journeys/customer-booking.spec.js` |
| TC-048 | Customer logout ends authenticated session | SEC, FUN, REG, E2E | Customer | Verify logout/session invalidation | Authenticated customer | Guest identity and login link return | Pass | Automated | `tests/journeys/customer-booking.spec.js` |
| TC-049 | Invalid Stripe session cannot create booking | INT, SEC, REG, E2E | Customer | Verify invalid payment confirmation handling | Customer Cart and invalid session ID | No confirmation; Cart remains available | Pass | Automated | `tests/integration/payment-boundaries.spec.js` |
| TC-050 | Cancelled payment preserves guest booking state | INT, REG, E2E | Guest | Verify cancellation handling | Guest Cart | Checkout returns and Cart remains available | Pass | Automated | `tests/integration/payment-boundaries.spec.js` |
| TC-051 | Successful Stripe payment persists booking | INT, REG, E2E | Customer | Verify paid booking persistence | Stripe test key and disposable database | Booking records and confirmation are created | Skipped | Automated | `tests/integration/payment-boundaries.spec.js` |
| TC-052 | Administrator can open the public homepage and retain their session | FUN, REG, E2E | Administrator | Verify "Public home" link renders flight workspace without ending admin session | Seeded `rmiller` account | Flight workspace visible; `#identity` contains `rmiller` | Pass | Automated | `tests/pages/administrator-pages.spec.js` |
| TC-054 | Guest completes the full pre-authentication booking journey | SMK, FUN, INT, REG, E2E | Guest | Verify the sequential guest journey from search through to the auth gate in one test | Seeded route 1 / 2026-10-29 | Auth gate with login and register options displayed | Pass | Automated | `tests/journeys/guest-booking.spec.js` |
| TC-055 | Guest Cart and passenger details are retained after login | FUN, INT, REG, E2E | Guest→Customer | Verify `restoreCustomerBookingState` transfers guest Cart and passenger details on login | Seeded `maevekelly` account; guest booking in progress | Cart flight and passenger details match pre-login state | Pass | Automated | `tests/journeys/guest-booking.spec.js` |
| TC-057 | Authenticated customer sees the Stripe payment button | FUN, INT, REG, E2E | Customer | Verify the Stripe button appears after complete Cart, seats and passenger details | Seeded `seanwalsh` account | "Pay securely with Stripe" button visible and enabled | Pass | Automated | `tests/journeys/customer-booking.spec.js` |
| TC-058 | Customer booking state persists through logout and login | FUN, INT, REG, E2E | Customer | Verify booking state round-trips through logout and re-login | Seeded `finnryan` account | Cart and passenger details match pre-logout state | Pass | Automated | `tests/journeys/customer-booking.spec.js` |
| TC-060 | Administrator adds a flight to the Cart | FUN, INT, REG, E2E | Administrator | Verify `rmiller` can search and add a flight from the public homepage | Seeded `rmiller` account; seeded flight data | Flight in Cart; admin identity unchanged | Pass | Automated | `tests/journeys/admin-booking.spec.js` |
| TC-061 | Administrator selects seats | FUN, INT, REG, E2E | Administrator | Verify `rmiller` can select seats and proceed to the passenger form | Admin session with flight in Cart | Passenger form displayed; admin identity unchanged | Pass | Automated | `tests/journeys/admin-booking.spec.js` |
| TC-062 | Administrator reaches the Stripe payment button | FUN, INT, REG, E2E | Administrator | Verify `rmiller` can complete passenger details and reach the payment button | Admin session with seat selected | Payment button visible; admin identity unchanged | Pass | Automated | `tests/journeys/admin-booking.spec.js` |
| TC-063 | Administrator Cart is not saved on logout | FUN, REG | Administrator | Document that admin Cart state is not persisted to `customer_booking_state` | Admin session with flight in Cart | Cart is empty after logout and re-login | Pass | Automated | `tests/journeys/admin-booking.spec.js` |
| TC-064 | Guest cannot access passenger details without seat selection | SEC, FUN, REG | Guest | Verify server-side seat-validation guard on `showPassengerDetails` | Guest session with flight in Cart, no seats selected | Seat selection page returned | Pass | Automated | `tests/security/access-control.spec.js` |
| TC-065 | Guest cannot initiate Stripe checkout directly | SEC, FUN, REG | Guest | Verify guest is prompted to authenticate before Stripe is called | Guest session | Login/register prompt shown; no Stripe redirect | Pass | Automated | `tests/security/access-control.spec.js` |
| TC-066 | Customer cannot post to admin mutation endpoints | SEC, FUN, REG | Customer | Verify `updateRoute` and `deleteSelectedRoutes` require admin | Customer session | Login page returned for both commands | Pass | Automated | `tests/security/access-control.spec.js` |
| TC-067 | stripeSuccess with valid-format session ID shows error and preserves Cart | INT, SEC, REG | Customer | Verify error handling when Stripe rejects the session and that Cart is preserved | Customer session with flight in Cart | Error message shown; Cart still contains the flight | Pass | Automated | `tests/security/access-control.spec.js` |
| TC-070 | Homepage is usable at 768px tablet viewport | FUN, RSP, REG | Guest | Verify flight workspace at tablet width | 768px viewport | No overflow; search controls visible | Pass | Automated | `tests/responsive/mobile.spec.js` |
| TC-071 | Cart page is usable at 390px | FUN, RSP, REG | Guest | Verify Cart layout at 390px | 390px viewport; flight in Cart | Cart items and total visible; no overflow | Pass | Automated | `tests/responsive/mobile.spec.js` |
| TC-072 | Cart page is usable at 320px | FUN, RSP, REG | Guest | Verify Cart layout at 320px | 320px viewport; flight in Cart | Cart items and total visible; no overflow | Pass | Automated | `tests/responsive/mobile.spec.js` |
| TC-073 | Checkout summary is readable at 320px | FUN, RSP, REG | Guest | Verify checkout summary and total at narrowest viewport | 320px viewport; complete checkout | Summary and total visible; no overflow | Pass | Automated | `tests/responsive/mobile.spec.js` |
| TC-076 | Flight manifest detail page shows passenger and seat data | FUN, INT, REG, E2E | Administrator | Verify manifest detail outputs route, revenue and passenger data | Admin session; seeded booking data | Route, revenue and customer data visible | Pass | Automated | `tests/pages/administrator-pages.spec.js` |

## Identifier audit

There are **59 declared tests in 10 files**. 56 are expected to pass; 2 are
intentionally skipped (TC-045, TC-051); 1 was added to the codebase before
this catalogue entry was written (TC-052, now recorded).

Inactive numeric ranges with no test claims:
TC-017 (commented experiment), TC-026–TC-030, TC-034, TC-039–TC-040,
TC-053 (not issued — redundant with TC-007 and TC-054),
TC-056 (reserved — disposable DB),
TC-059 (absorbed into TC-052 extension),
TC-068 (reserved — disposable DB),
TC-069 (reserved — investigate server behaviour first),
TC-074 (reserved — disposable DB),
TC-075 (reserved — disposable DB),
TC-077 (reserved — Stripe + disposable DB),
TC-078 (reserved — Stripe + disposable DB).

## Category execution

All tests use exact Playwright tags: `@SMK`, `@FUN`, `@INT`, `@REG`, `@SEC`,
`@RSP` and `@E2E`. Category commands are:

```powershell
npx playwright test --grep "@SMK"
npx playwright test --grep "@FUN"
npx playwright test --grep "@INT"
npx playwright test --grep "@REG"
npx playwright test --grep "@SEC"
npx playwright test --grep "@RSP"
npx playwright test --grep "@E2E"
```

## Coverage gaps and safety

- Successful Stripe payment, booking persistence and duplicate confirmation
  require Stripe test credentials (sk_test_) and a disposable database (TC-077, TC-078).
- TC-056 and TC-068 (registration persistence and guest-Cart-on-registration)
  require a disposable database (`PW_ALLOW_DB_MUTATIONS=true`).
- Route creation, editing and deletion (TC-074, TC-075) are not run against
  the working database.
- Suspended-account enforcement is not implemented by the current
  authentication method (TC-045 skipped).
- Tablet viewport coverage at 768px is now implemented (TC-070).
- AC220 renderer grouping does not match the database A–F seat definitions.
- Ordinary tests use existing seeded records and must not import, drop or
  recreate the working database.
- TC-067 is filed under `tests/security/access-control.spec.js`. The session ID
  `cs_test_valid_format_not_real_XYZ123` passes the StripeGateway regex check,
  causing a real Stripe API call which Stripe rejects, triggering the catch block.
  This tests the Stripe-error path; the empty-Cart guard (line 799 of
  controller.php) is not independently reachable via the UI.