# Airline2026

Airline2026 restores and develops an original 2014 PHP airline booking application. It preserves the historical server-rendered MVC style while providing customer booking, passenger and seat handling, and administrator route and manifest workflows.

The original application was developed as part of the **Higher Diploma in Cloud and Mobile with Data Science**. This project is a local restoration and development reference, not a replacement rewrite. Its interface and naming retain many conventions from the 2014 application.


<img src="assets/Images/newHome.jpg" alt="Home Cart screen" width="80%">
<img src="assets/Images/newCart.jpg" alt="Home Cart screen" width="40%">
<img src="assets/Images/newCheckout.jpg" alt="Home Checkout screen" width="40%">

## Technology

- PHP with PDO
- MySQL 5.7 for the current local database
- Server-rendered HTML forms and PHP views
- Light vanilla JavaScript for the trip-type tabs and planned seat-selection interactions
- CSS in [`mystyles.css`](mystyles.css) and [`insidestyles.css`](insidestyles.css)
- PHP sessions and cookies for login and Cart state

The original SQL dump was produced in the PHP 5.4.19, MySQL 5.5.32, and phpMyAdmin 4.0.4.1 era. The current standalone database script targets MySQL 5.7. The application remains primarily server-rendered; there is no separate JavaScript application in this repository.

## Functionality

### Customer booking workflow

The current PHP source retains the original customer-facing workflow and interface:

- Three-pane flight workspace:
  1. **Search Flights** — trip type, route, travel date and passenger/ticket count.
  2. **Available Flights** — matching flight options.
  3. **Selected Flight Details** — details of the selected option and the **Add to Cart** action.
- Trip-type tabs: **One Way** (active), Round Trip and Multi-City (placeholders marked “Coming Soon”).
- Route search by origin, destination, travel date and number of tickets.
- Flight selection and session-backed Cart handling.
- Cart review, item removal and the existing legacy seat-selection interface.
- Customer registration, guest access, login and logout.
- Passenger name, passport, email and payment-detail forms.
- Legacy passenger and seat information written to the database.

The current PHP search and seat interfaces still contain legacy behaviour. In particular, the current search has not yet been fully migrated to use the dated `flights` table, and the active Cart interface displays 20 legacy seat positions. The full dated-flight and aircraft-specific seat-map workflow described later in this README is planned development, not a claim about the current implementation.

### Administrator workflow

The existing PHP application provides administrator workflows to:

- Log in to the administration area.
- View active routes.
- Add routes with operating dates and prices.
- Edit route prices.
- Delete routes.
- View the flight manifest.
- Open flight details, including passenger and seat information.
- View route and passenger data used to calculate flight revenue.

The administrator account is `rmiller` / `rmiller`. The PHP controller authorizes this account by username. The database also retains the historical `login` table for compatibility.

## Colour scheme

There are already many colours in a garden, so the application uses a restrained palette: a primary colour for text and headings, a secondary colour for calls to action (CTAs), and whitespace where appropriate.

The main page uses the **rule of thirds** to create a balanced and visually interesting composition.

[Image Color Picker](https://imagecolorpicker.com/) was used to help select the colour palette.

The original image is:

<details>
<summary>Click to view the original image</summary>

<img src="assets/Images/newHome.jpg" alt="Original home page" style="width:60%;">

</details>

The resulting colour palette is shown below.

<div style="margin-left: 40px;">
  <img src="./Palette.png" alt="Colour palette swatch" style="width:60%;">
</div>

<details>
<summary>Colour palette details</summary>

| Colour | Hex | Usage |
|---|---|---|
| Dark Forest Green | `#3B4D32` | Primary green / headings |
| Deep Green | `#2A5B44` | CTA / secondary colour |
| Off-White | `#F5F5F0` | Main background |
| Charcoal Blue-Grey | `#303B41` | Primary text |
| Muted Grey | `#878679` | Secondary text / subtle details |
| Dark Olive | `#444235` | Dark accents |
| Sage Grey | `#B3B8AB` | Borders and background areas |
| Muted Blue-Grey | `#738B8F` | Highlight links and hover effects |
| Deep Teal Green | `#33524E` | Supporting accents |

</details>

CSS custom properties use descriptive names rather than hexadecimal values as variable names. The palette can be represented in CSS as follows:

```css
:root {
    --forest-green: #3b4d32;
    --deep-green: #2a5b44;
    --off-white: #f5f5f0;
    --charcoal-blue-grey: #303b41;
    --muted-grey: #878679;
    --dark-olive: #444235;
    --sage-grey: #b3b8ab;
    --muted-blue-grey: #738b8f;
    --deep-teal-green: #33524e;
}
```

### Seat-map state colours

The aircraft seat map will use a dedicated set of state colours that remains visually consistent with the wider Airline2026 palette:

| Seat state | Colour | Hex | Meaning |
|---|---|---|---|
| Available | Green | `#386747` | Seat can be selected |
| Selected | Blue | `#2563EB` | Seat currently selected by this customer |
| Occupied / unavailable | Grey | `#B8B8B8` | Seat is already allocated and cannot be selected |
| Premium | Yellow | `#F4C542` | Premium seat; premium status is independent of occupancy |
| Hover / keyboard focus | Dark green outline | `#294D35` | Highlights the seat currently under interaction |

Premium status and seat availability are separate properties. For example, an available premium seat is yellow; once selected it uses the selected blue state; if occupied it uses the unavailable grey state. A legend will explain the states beside the seat map.

The general page palette remains in use for the surrounding interface:

| Interface element | Colour |
|---|---|
| Main background | `#F5F5F0` |
| Primary headings | `#3B4D32` |
| Primary text | `#303B41` |
| CTA buttons | `#2A5B44` |
| Borders and separators | `#B3B8AB` |
| Secondary text | `#878679` |

## Typography

Airline2026 uses two sans-serif font families to provide a clear visual hierarchy and consistent presentation across public pages, passenger booking workflows and administrator views.

- **Primary — Roboto:** body text, forms, flight results, passenger details, seat labels and booking information. Its legibility makes it suitable for information-dense interfaces.
- **Secondary — Montserrat:** page headings, section titles and selected navigation headings. Its geometric style gives headings a distinctive appearance while complementing the primary font.

Both fonts use sensible fallback fonts to maintain readability if the preferred fonts are unavailable.

```css
:root {
    --font-primary: "Roboto", "Segoe UI", Arial, sans-serif;
    --font-secondary: "Montserrat", "Roboto", "Segoe UI", Arial, sans-serif;
}
```

The colour palette and typography are intended to work together to create a clear, accessible and consistent interface without unnecessarily changing the original application's layout and structure.

## Screenshots

These existing captures document the original customer and administration workflows.

### Customer workflow

| Screen | Description |
|---|---|
| ![Home screen](assets/Images/homescreen.jpg) | Guest home screen with route, ticket and date search |
| ![Login](assets/Images/login.jpg) | Customer login form |
| ![Registration](assets/Images/register.jpg) | Customer registration form |
| ![Logged in](assets/Images/logged-in.jpg) | Search screen for a logged-in customer |
| ![Search](assets/Images/search.jpg) | Route search |
| ![Search results](assets/Images/search-results.jpg) | Available flight results |
| ![Availability](assets/Images/availability.jpg) | Legacy seat availability |
| ![Booked seats](assets/Images/seats-booked.jpg) | Seat allocation after booking |
| ![Booked seats PNG](assets/Images/seats-booked.png) | Additional seat-allocation capture |
| ![Purchase complete](assets/Images/purchased.jpg) | Completed purchase screen |

### Administration workflow

| Screen | Description |
|---|---|
| ![Edit routes](assets/Images/admin-view-edit-routes.jpg) | Administrator route list and editing options |
| ![Add routes](assets/Images/admin-add-routes.jpg) | Add-route form |
| ![Add route](admi-add-route.jpg) | Additional add-route capture (legacy filename) |
| ![Flight manifest](assets/Images/admi-view-mainfest.jpg) | Flight manifest and administration view (legacy filename) |

## Database installation

[`airline2026.sql`](airline2026.sql) is the **single, self-contained database installation script**.

It creates the `airline2026` database from scratch, creates its tables, relationships, indexes, trigger and constraints, and inserts the initial fictional test data. The database is not installed through an initial import followed by a separate update script.

The script is destructive: it drops and recreates the existing `airline2026` database. Any existing data in that database will be lost. It does not need the historical backup to run. The separate `airline2013` database is not the target of this setup.

### Running the script locally

From the project root, use the following command in the VS Code PowerShell terminal:

```powershell
Get-Content ".\airline2026.sql" | & "C:\Program Files\MySQL\MySQL Server 5.7\bin\mysql.exe" -u root -p -h 127.0.0.1 -P 3307
```

Enter the MySQL root password when prompted.

This PowerShell command streams the local SQL file to the MySQL client. The script itself is responsible for creating the database, tables, constraints and initial data.

### Flight-code generation and duplicate prevention

The flight seed data generates flight codes using route identity and flight date. This addresses the earlier duplicate-entry error involving `PA101` on `2026-10-24`, which violated the unique `uq_flights_code_date` constraint.

The corrected seed query also uses `SELECT DISTINCT` to eliminate duplicate rows from its selected results. The unique constraint on flight code and flight date remains in place as a database-level safeguard.

`SELECT DISTINCT` does not replace the unique constraint: the generated values must still satisfy the database's uniqueness rules.

### Verified local import results

The corrected script completed successfully in the local MySQL 5.7 environment. Its verification output reported the following table counts:

| Table | Reported rows |
|---|---:|
| `aircraft` | 2 |
| `aircraft_seats` | 400 |
| `booking` | 3 |
| `booking_flights` | 3 |
| `flights` | 2,788 |
| `login` | 3 |
| `passangers` | 6 |
| `routes` | 12 |
| `user` | 14 |

These are the counts reported by the local import verification query. MySQL table statistics can be approximate where InnoDB statistics are used.

The aircraft verification output reported:

| Aircraft code | Capacity | Seat total | Premium seats |
|---|---:|---:|---:|
| `AC180` | 180 | 180 | 18 |
| `AC220` | 220 | 220 | 18 |

The SQL script also emits verification output for table counts, aircraft seat totals and duplicate passenger-seat groups.

### Verifying the database

After a successful import, connect to MySQL and run:

```sql
USE airline2026;
SHOW TABLES;

SELECT flight_code, flight_date, COUNT(*) AS duplicates
FROM flights
GROUP BY flight_code, flight_date
HAVING COUNT(*) > 1;
```

The final query should return an empty result set if no duplicate flight-code/date combinations exist.

To verify aircraft seat totals:

```sql
SELECT
    aircraft_code,
    capacity,
    COUNT(aircraft_seats.aircraft_id) AS seat_total,
    SUM(is_premium) AS premium_total
FROM aircraft
LEFT JOIN aircraft_seats USING (aircraft_id)
GROUP BY aircraft_id, aircraft_code, capacity;
```

The expected configurations are 180 seats for `AC180` and 220 seats for `AC220`, with 18 premium seats per aircraft.

## Tables and relationships

| Table | Purpose and important columns |
|---|---|
| `login` | Historical login-compatible username/password rows |
| `user` | Customer accounts: `id`, `username`, `password`, `email`, `CreditCardNo`, `passport`, `suspend` |
| `routes` | Route definitions: `id`, `source`, `dest`, `start`, `end`, `price` |
| `aircraft` | Aircraft configurations: `aircraft_id`, `aircraft_code`, `model`, `configuration_name`, `capacity` |
| `aircraft_seats` | Individual seats: `aircraft_id`, `seat_no`, `seat_label`, `seat_row`, `seat_position`, `is_premium` |
| `flights` | Dated departures: `flight_id`, `route_id`, `flight_date`, `flight_code`, `aircraft_id`, scheduled and expected times, `capacity`, `price`, `status` |
| `booking` | Booking header and optional customer link: `booking_id`, `booking_reference`, `user_id`, `booking_status`, `created_at` |
| `booking_flights` | Ordered booking-to-flight links: `booking_flight_id`, `booking_id`, `flight_id`, `sequence_no` |
| `passangers` | Legacy-spelled passenger records: `id`, `name`, `passport`, `route`, `date`, `registered`, `registeredName`, `creditCardno`, `email`, `seatNo`, `flight_id`, `booking_flight_id` |

Routes own flights. Flights reference routes and aircraft. Aircraft own their seat definitions. Bookings reference users where applicable and link to flights through `booking_flights`. Passenger records can reference routes, flights and optional booking-flight rows.

The unique `passangers(flight_id, seatNo)` key prevents the same seat from being recorded twice on a flight. A trigger fills `flight_id` for compatible legacy PHP inserts that provide only route and date information.

The database includes fictional 180-seat `AC180` and 220-seat `AC220` configurations. Individual seats have visual labels such as `1A`; seats 1 through 18 are marked premium. Flight capacity is intended to match the assigned aircraft.

## Run locally

The traditional PHP entry point is `controller.php`.

1. Install PHP with the PDO MySQL extension and a local MySQL 5.7 server.
2. Run [`airline2026.sql`](airline2026.sql) using the installation command above.
3. From the project root, start the PHP development server:

   ```powershell
   & "C:\php-8.4.26\php.exe" -S localhost:8000
   ```

4. Open [http://localhost:8000/controller.php](http://localhost:8000/controller.php).
5. Test guest route search, registration/login, Cart handling, passenger submission, and the administrator route/manifest pages.

Use the fictional accounts in the SQL data for testing. The suspended sample user is `eoinnolan`; the current PHP authentication method does not enforce the `suspend` flag, so suspension is stored data rather than a complete application feature.

The local PHP configuration in [`library.php`](library.php) uses host `127.0.0.1`, port `3307`, database `airline2026`, user `root`, and the configured local password. Check the local configuration on your own machine; development credentials must not be used in production.

## Project structure

- [`controller.php`](controller.php) — central GET/POST command router and request handlers
- [`library.php`](library.php) — view classes, Cart behaviour, seat processing and `DBManager`. View classes use a `Public*` / `Customer*` / `Admin*` naming scheme. See [View class hierarchy](#view-class-hierarchy).
- [`airline2026.sql`](airline2026.sql) — standalone MySQL 5.7 database schema and test data
- [`mystyles.css`](mystyles.css) — customer-facing styles
- [`insidestyles.css`](insidestyles.css) — internal and administration styles
- [`assets/Images/`](assets/Images/) — existing application screenshots
- [`old/airline2026_backup.sql`](old/airline2026_backup.sql) — historical backup/reference only

The original interface uses a fixed-width layout of approximately 800 pixels and traditional CSS floats rather than a modern responsive framework. `controller.php` dispatches requests to the views and handlers in `library.php`; supporting classes handle page presentation, Cart operations, seat processing and database access.

## View class hierarchy

Public and private views are separate hierarchies with their own base class and stylesheet (`mystyles.css` and `insidestyles.css`).

```text
PublicBaseView (abstract)
├── PublicHomeView
├── LoginView
└── RegistrationView

PrivateBaseView (abstract, standalone, was AuthenticatedBaseView)
├── CustomerBaseView (abstract)
│   └── CustomerView (was FlightSearchView)
│       ├── CustomerFlightResultsView (was FlightSearchResultsView)
│       ├── CustomerPassengerDetailsView (was PassengerDetailsView)
│       └── CustomerCheckoutView (was CheckoutView)
└── AdminBaseView (abstract)
    ├── AdminRoutesView
    │   └── AdminView (was InnerAdminView)
    ├── AdminEditRouteView (was EditRouteView)
    ├── AdminAddRouteView (was AddRouteView)
    └── AdminFlightManifestView (was FlightManifestView)
```

### Customer and Guest Home Pages — Three-Pane Layout

The customer and guest home pages use a three-pane flight workspace:

1. **Search Flights** — Contains the One Way, Round Trip and Multi-City tabs, route selector, travel date and passenger count.
2. **Available Flights** — Lists matching flight options.
3. **Selected Flight Details** — Displays the currently selected flight and provides the **Add to Cart** action.

**Current functionality:**

- **One Way:** the active search workflow, including route search and flight selection.
- **Round Trip:** visual placeholder displaying a “Coming Soon” message.
- **Multi-City:** visual placeholder displaying a “Coming Soon” message.

Only One Way searching is currently implemented. Round Trip and Multi-City functionality will be developed in a future update. The dated-flight search integration remains part of the planned work described below.

## Planned booking enhancements

The database now provides `booking`, `booking_flights` and dated `flights` structures that can support future itinerary work. Potential future enhancements include:

- Full Round Trip and Multi-City booking flows.
- Return-date and multi-leg selection.
- Grouping several selected flights into one booking.
- More robust validation and consistent use of prepared statements.
- Replacing the legacy 20-position seat interface with aircraft-specific 180/220-seat layouts.
- A responsive interactive seat-selection page integrated with the Cart and booking process.

These are planned enhancements, not claims about functionality currently implemented by the PHP source.

## Proposed Cart and Seat Selection Enhancements

The next stage of development will focus on completing the Cart and reservation workflow. The intention is to extend the existing session-backed `Cart` class while retaining the original PHP server-rendered MVC architecture.

### 1. Cart — proposed design

The Cart will act as a temporary reservation basket rather than a conventional online shopping cart.

It will hold the flights selected by the customer, the travel date, passenger count and, when implemented, any seat selections made during the reservation process.

**Proposed Cart responsibilities:**

- Add selected flights to the Cart.
- Display flight details, including airline, flight code, route, date, departure and arrival times.
- Display passenger count and fare total.
- Allow customers to remove individual flights.
- Retain passenger and seat-selection information as the booking workflow develops.
- Display selected seat numbers and associated fare information when seat selection is implemented.
- Proceed to Checkout when the reservation is ready.

The existing session-backed Cart will be retained. No additional database table is currently proposed for temporary Cart contents.

### 2. Proposed booking workflow

The intended customer journey is:

```text
          SEARCH FLIGHTS
                |
                v
       SELECT AVAILABLE FLIGHT
                |
                v
            ADD TO CART
                |
                v
       +---------------------+
       |         CART        |
       |                     |
       | Review flight       |
       | Check passenger     |
       | count and fare      |
       | Remove flight       |
       +---------------------+
                |
                v
             CHECKOUT
                |
                v
        PASSENGER DETAILS
                |
                v
       SEAT SELECTION
       (initially a seat-number
        placeholder; graphical
        map in a later stage)
                |
                v
       VALIDATE AVAILABILITY
                |
                v
        CONFIRM RESERVATION
                |
                v
          CREATE BOOKING
                |
                v
       BOOKING CONFIRMATION
```

A flight added to the Cart is not a confirmed booking. The Cart is temporary session state. A booking is created only after the customer confirms and the application successfully validates and records the reservation.

### 3. Guest and customer Cart handling

Guests will be able to search for flights, select flights and add flights to the Cart without first registering or logging in.

If a guest subsequently logs in or registers, the existing Cart and flight-search state should be preserved.

```text
         GUEST SEARCH
              |
              v
          ADD TO CART
              |
              v
          GUEST CART
              |
              v
        LOGIN / REGISTER
              |
              v
     AUTHENTICATED CUSTOMER
              |
              v
       RESTORE CART STATE
              |
              v
           CHECKOUT
```

Authentication must not discard the guest's selected flights, Cart contents or current flight-search selection.

### Shopping Cart – Initial Implementation

The legacy cart has been replaced with a session-based Cart class.

Current functionality:
- Add selected flights to the cart.
- Remove individual flights.
- Display flight details and passenger quantities.
- Calculate and display the cart total.
- Synchronise the home-page flight indicator with cart additions and removals.
- Allow Guest users to build a cart without authentication.

Next development stages:
- Passenger details.
- Interactive aircraft seat selection for AC180 and AC220.
- Seat availability and booking validation.
- Checkout authentication.
- Booking confirmation and database persistence.

### 4. Dynamic aircraft seat selection

The existing Cart interface uses a legacy 20-position representation. This will eventually be replaced by an aircraft-specific seat map based on the aircraft assigned to each dated flight.

The database already contains the necessary structures:

- `aircraft` — aircraft model and seating capacity.
- `aircraft_seats` — individual seat definitions, labels, rows, positions and premium-seat indicators.
- `flights` — the aircraft assigned to each dated flight.
- `passangers` — passenger and allocated seat information.

The application will retrieve the seat configuration using the selected flight's `aircraft_id`. This allows the same booking workflow to display the appropriate layout for either aircraft.

| Aircraft | Total capacity | Premium seats | Standard seats |
|---|---:|---:|---:|
| `AC180` | 180 | 18 | 162 |
| `AC220` | 220 | 18 | 202 |

Seat positions, labels and premium classification will be read from `aircraft_seats`, rather than being hard-coded as a fixed collection of HTML radio buttons.

### 5. Aircraft layouts

Two distinct top-down aircraft layouts will be created, one for `AC180` and one for `AC220`. Both will share the same visual language, seat-state colours, interaction model and responsive behaviour.

Each layout will show:

- Aircraft nose and cockpit at the front.
- Fuselage outline and tail at the rear.
- Wings positioned in relation to the fuselage.
- Seating sections and row numbers.
- Individual labelled seats.
- A clearly visible central aisle separating the left and right groups of three seats.
- Premium seating indicated using the agreed premium colour.
- A legend explaining available, selected, occupied and premium states.

The intended six-seat row arrangement is **3–aisle–3**:

```text
                 FRONT / COCKPIT
                    _______
                  /         \
                 /           \
                |  A B C D E F |
                |  □ □ □   □ □ □ |  Row 01
                |  □ □ □   □ □ □ |  Row 02
                |  □ □ □   □ □ □ |  Row 03
                |  □ □ □   □ □ □ |  Row 04
                |       ...      |
                |  □ □ □   □ □ □ |
                 \             /
                  \___________/
                    REAR / TAIL

                 LEFT  AISLE  RIGHT
                  3      |      3
```

The diagram is conceptual. The actual seat labels, positions and premium classification will be generated from the database configuration.

**AC180 layout**

- 180 seats in total.
- 30 rows of six seats.
- Three seats on each side of the central aisle.
- 18 premium seats, as defined by the database.

**AC220 layout**

- 220 seats in total.
- 36 complete rows of six seats (216 seats), plus four seats in the final row.
- The final row will be balanced around the central aisle where the database configuration permits.
- 18 premium seats, as defined by the database.

The database remains the source of truth for the exact seat labels, row positions and premium indicators. The layout must not assume that every aircraft has an identical seat configuration.

### 6. Seat-map colour coding

The agreed seat-state colours are:

| Seat state | Appearance | Colour |
|---|---|---|
| Available | Green seat | `#386747` |
| Selected by customer | Blue seat | `#2563EB` |
| Occupied / unavailable | Grey seat | `#B8B8B8` |
| Premium | Yellow seat | `#F4C542` |
| Hover / keyboard focus | Dark green outline | `#294D35` |

Premium status and availability are independent. A premium seat is yellow while available; it changes to blue when selected and grey when occupied. This prevents the premium classification from obscuring whether a seat can be selected.

The seat map will include a visible legend. Colour will not be the only means of communicating state: accessible labels and appropriate text or symbols will also identify seat status.

### 7. Responsive design

Both aircraft layouts must be fully responsive and usable on desktop, tablet and mobile screens. The seat map must not simply shrink a desktop aircraft diagram until seat labels and controls become too small to use.

The responsive implementation will consider:

- **Desktop:** show the aircraft outline and seat arrangement with clear row numbers, labels, aisle and wing positions.
- **Tablet:** adjust the aircraft map and surrounding controls to use the available width without making seats difficult to select.
- **Mobile:** use a compact layout with touch-friendly seat controls, readable labels and a clearly visible aisle. Keep the seat legend and booking controls accessible.
- Avoid unnecessary horizontal scrolling. If the available screen width requires a compact or horizontally navigable map, make that behaviour deliberate and usable.
- Maintain visible keyboard focus and sufficient contrast.
- Keep the selected-seat summary and passenger progress clear at all screen sizes.

The two aircraft designs will have different overall dimensions and row counts, but will use the same responsive design principles.

### 8. Seat-map implementation approach

The aircraft outline and the interactive seat controls will be separate parts of the interface.

The proposed implementation is:

- **SVG:** aircraft structure, including fuselage, nose, tail and wings.
- **HTML:** individual interactive seat elements and accessible seat labels.
- **CSS Grid/Flexbox:** seat rows, left and right seat groups, aisle spacing and layout.
- **CSS media queries:** desktop, tablet and mobile presentation.
- **Vanilla JavaScript:** immediate seat-selection feedback and client-side interaction.
- **PHP:** retrieve the aircraft configuration, render the page and integrate seat selections with the session-backed Cart.
- **MySQL:** authoritative aircraft configuration and confirmed seat allocations.

The seat map will not be a static aircraft image with image hotspots. Each seat will be a real HTML control so that it can be selected, labelled, styled and made accessible.

The AC180 and AC220 layouts will be separate aircraft configurations using shared rendering and interaction principles. The selected flight's `aircraft_id` will determine which configuration is displayed.

### 9. Seat-selection behaviour

The proposed interaction is:

1. The customer opens the Cart or the seat-selection stage of the booking workflow.
2. The application retrieves the aircraft configuration for each selected flight.
3. The application retrieves existing passenger seat allocations for that flight.
4. Available seats are displayed as selectable controls.
5. Occupied seats are displayed in grey and cannot be selected.
6. Premium seats are identified in yellow while available.
7. The customer selects one seat for each passenger.
8. The selected seats are retained in the session-backed Cart until booking confirmation.

The interface should prevent a customer from selecting more seats than the passenger count. It should also make it clear which passenger is associated with each selected seat.

Seat availability must be checked again when the booking is confirmed. Another customer may have booked a seat while the current customer was completing the reservation. The browser-side display is not authoritative.

The existing unique database constraint on `passangers(flight_id, seatNo)` provides an additional safeguard against duplicate seat allocations.

### 10. Booking persistence

The existing database schema supports the proposed booking process.

```text
              booking
                 |
                 | booking_id
                 v
           booking_flights
                 |
                 | flight_id
                 v
               flights
                 |
                 | aircraft_id
                 v
              aircraft
                 |
                 v
           aircraft_seats

           booking_flights
                 |
                 v
             passangers
```

On confirmation, the application will:

- Revalidate the selected flight and seats against current database records.
- Create the booking record.
- Associate the selected flights through `booking_flights`.
- Record passenger and seat information in `passangers`.
- Use a database transaction so that incomplete booking operations can be rolled back.
- Clear the temporary Cart only after successful confirmation.

No additional database tables are currently proposed for the initial implementation. The existing tables and relationships will be used unless development reveals a specific requirement that cannot be met by the current schema.

### 11. Seat-allocation safeguards

The seat map is an interactive representation of database availability, not a reservation lock.

The final booking operation must:

- Confirm that the flight still exists and is bookable.
- Confirm that each selected seat belongs to the aircraft assigned to that flight.
- Confirm that no selected seat has already been allocated to another passenger on that flight.
- Confirm that the number of allocated seats does not exceed the passenger count.
- Insert the booking, flight links and passenger allocations within a database transaction.
- Roll back the transaction if any validation or insert fails.
- Retain the Cart if confirmation fails, so the customer can correct the reservation.

The unique key on `passangers(flight_id, seatNo)` remains the database-level duplicate-allocation safeguard. PHP must handle a duplicate-key failure cleanly rather than treating the browser's earlier availability display as a guarantee.

### 12. Implementation sequence

The enhancements will be introduced incrementally, preserving the existing class hierarchy and separation between public, customer and administrator views.

The proposed sequence is:

1. **Cart foundation:** refine the existing `Cart` class and implement reliable flight addition, removal and Cart display.
2. **Booking placeholders:** retain passenger and seat-number placeholders so the Cart and booking workflow can be tested before the graphical map is introduced.
3. **Passenger and Cart integration:** connect passenger count, passenger details and Cart state without changing the existing MVC architecture.
4. **Aircraft layout design:** create the separate AC180 and AC220 top-down layouts, including the fuselage, wings, aisle and seat positions.
5. **Responsive seat-map interface:** implement the HTML seat controls, agreed colour states, legend and desktop/tablet/mobile presentation.
6. **Session integration:** connect selected seats to the appropriate passenger and flight in the session-backed booking state.
7. **Booking confirmation:** implement final database validation, transactional booking persistence and duplicate-seat handling.

This sequence allows the Cart and booking workflow to be completed and tested before the more involved graphical seat-map interface is integrated.

### 13. Scope and compatibility

The enhancements will preserve:

- The existing PHP server-rendered MVC approach.
- The current `PublicBaseView`, `PrivateBaseView`, customer and administrator class hierarchy.
- The separate public/customer and internal/administrator stylesheets.
- The existing MySQL tables and relationships.
- The legacy `passangers` table spelling for compatibility.
- The established Airline2026 colour palette and typography.

The seat-map work is an extension of the current application, not a redesign of its backend or a move to a separate JavaScript framework.

### 14. Current implementation versus proposed work

The following distinction is important:

| Area | Current position |
|---|---|
| Session-backed Cart | Existing Cart functionality is present; further refinement is planned |
| Legacy seat interface | Displays 20 seat positions |
| Aircraft database | `AC180` and `AC220` configurations are present |
| Aircraft seat definitions | Stored in `aircraft_seats` |
| Full 180/220-seat graphical maps | Proposed; not yet implemented |
| Responsive interactive seat map | Proposed; not yet implemented |
| Seat selection retained in Cart | Planned integration |
| Transactional booking confirmation | Planned enhancement |

The proposed seat-map layouts, colours and workflow describe the intended development direction. They should not be interpreted as features already delivered by the current PHP source.

## Backup and limitations

[`old/airline2026_backup.sql`](old/airline2026_backup.sql) is a retained historical backup/reference dump. It is not required for installation and must not be run in sequence with the standalone script.

Known limitations include:

- The active Cart UI displays only 20 legacy seat positions.
- PHP does not yet render the full 180/220-seat aircraft maps.
- The current PHP search has not yet been fully migrated to query the dated `flights` table.
- Passwords are stored and compared in plain text for legacy compatibility.
- Several PHP queries use string interpolation rather than prepared statements.
- The `suspend` field is stored but is not enforced by the current authentication method.
- Payment details are fictional test data; there is no production payment processing.
- The project is not a production-ready airline operations or booking system.

Before real-world use, the application would require a security and reliability review, secure credential handling, parameterized queries, protection of customer/payment data, and modernization of legacy PHP/database conventions.

## Refactor history

In 2026 the view classes were renamed for clarity and the private view tree was made independent of the public one, with no change in behaviour. The original 2014 names are retained here for reference.

| 2014 class name | Current class name |
|---|---|
| `AuthenticatedBaseView` | `PrivateBaseView` |
| `FlightSearchView` | `CustomerView` |
| `FlightSearchResultsView` | `CustomerFlightResultsView` |
| `PassengerDetailsView` | `CustomerPassengerDetailsView` |
| `CheckoutView` | `CustomerCheckoutView` |
| `InnerAdminView` | `AdminView` |
| `EditRouteView` | `AdminEditRouteView` |
| `AddRouteView` | `AdminAddRouteView` |
| `FlightManifestView` | `AdminFlightManifestView` |
| `PublicBaseView` | `PublicBaseView` (unchanged) |
| `PublicHomeView` | `PublicHomeView` (unchanged) |
| `LoginView` | `LoginView` (unchanged) |
| `RegistrationView` | `RegistrationView` (unchanged) |
| `CustomerBaseView` | `CustomerBaseView` (unchanged) |
| `AdminBaseView` | `AdminBaseView` (unchanged) |
| `AdminRoutesView` | `AdminRoutesView` (unchanged) |
| `Cart` | `Cart` (unchanged) |
| `DBManager` | `DBManager` (unchanged) |

`AdminView` is a thin subclass of `AdminRoutesView`, retained from the original `InnerAdminView / AdminRoutesView` arrangement.

A shared `FlightWorkspace` trait was later introduced to provide the three-pane search interface for both guest and customer views without duplicating markup.
