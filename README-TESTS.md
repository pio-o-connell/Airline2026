# Airline2026 Playwright tests

This suite tests the real server-rendered Airline2026 application. It follows
the organisation and reporting conventions of the Blogger-2026 Playwright suite
but has independent configuration, helpers, selectors and test data.

## Install and run

From the Airline2026 project root:

```powershell
npm ci
npx playwright install chromium
npm test
```

The Playwright configuration starts PHP at `http://127.0.0.1:8000` using
`C:\php-8.4.26\php.exe`. Set `PW_BASE_URL` when using a different server.

The default seeded accounts are read-only test accounts:

```powershell
$env:PW_AIRLINE_USERNAME = "aoifebyrne"
$env:PW_AIRLINE_PASSWORD = "aoife2026"
$env:PW_AIRLINE_SECOND_USERNAME = "cianmurphy"
$env:PW_AIRLINE_SECOND_PASSWORD = "cian2026"
$env:PW_AIRLINE_SUSPENDED_USERNAME = "eoinnolan"
$env:PW_AIRLINE_SUSPENDED_PASSWORD = "eoin2026"
$env:PW_AIRLINE_ADMIN_USERNAME = "rmiller"
$env:PW_AIRLINE_ADMIN_PASSWORD = "rmiller"
```

Override these variables for a disposable test database. Do not put credentials
or Stripe keys in source control. These defaults use the existing local database
records; the Playwright suite does not create users, change passwords, add bcrypt,
or alter the schema.

## Test files

- `tests/pages/public-pages.spec.js` — homepage, search, selection and guest Cart
- `tests/pages/booking-workflow.spec.js` — Cart totals, removal, seats and checkout
- `tests/pages/authentication.spec.js` — customer login and registration form
- `tests/pages/administrator-pages.spec.js` — administrator access and read-only views
- `tests/responsive/mobile.spec.js` — 390px and 320px checks
- `tests/helpers/airline.js` — shared search, Cart and authentication helpers

The current suite has 35 passing tests and 2 intentionally skipped tests. It
includes multi-flight Cart and seat coverage, passenger validation, checkout
summaries, customer booking flow, direct administrator access checks, payment
boundary checks and responsive booking flows. AC220 coverage records the
current renderer discrepancy: the database defines 37 rows of six seats, while
the renderer currently displays 22 rows of ten seats.

Additional coverage is organised as follows:

- `tests/journeys/customer-booking.spec.js` — registered customer workflow,
  checkout state retention, validation and logout.
- `tests/security/access-control.spec.js` — direct administrator access,
  customer access denial and invalid credentials.
- `tests/integration/payment-boundaries.spec.js` — invalid and cancelled
  payment handling, with successful payment persistence reserved for a
  disposable database.
- `TEST-CATALOGUE.md` — coverage mapping, test-data policy and known gaps.

Stripe payment, duplicate confirmation, registration persistence, saved booking
state restoration, and route creation/editing are setup-dependent and remain
separate follow-up coverage. Ordinary test execution never imports or resets the
working database.
