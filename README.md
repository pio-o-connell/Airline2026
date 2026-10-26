# Airline2026

Airline2026 restores and develops an original 2014 PHP airline booking application. It preserves the historical server-rendered MVC style while providing customer booking, passenger and seat handling, and administrator route and manifest workflows.

The original application was developed as part of the **Higher Diploma in Cloud and Mobile with Data Science**. This project is a local restoration and development reference, not a replacement rewrite. Its interface and naming retain many conventions from the 2014 application.

![Home screen](assets/Images/newHome.jpg)

## Technology

- PHP with PDO
- MySQL 5.7 for the current local database
- Server-rendered HTML forms and PHP views
- JavaScript where present in the legacy interface
- CSS in [`mystyles.css`](mystyles.css) and [`insidestyles.css`](insidestyles.css)
- PHP sessions and cookies for login and cart state

The original SQL dump was produced in the PHP 5.4.19, MySQL 5.5.32, and phpMyAdmin 4.0.4.1 era. The current standalone database script targets MySQL 5.7. The application remains primarily server-rendered; there is no separate JavaScript application in this repository.

## Functionality

### Customer booking workflow

The existing PHP source implements the following customer workflow:

- Search routes by origin, destination, travel date, and number of tickets
- Display matching route and travel-date results
- Check availability using the legacy 20-seat flight model
- Add a provisional flight to the session-backed cart
- Select seats, review cart items, update seats, and remove cart items
- Register a customer account or continue as a guest
- Log in and log out
- Enter passenger name, passport, email, and payment details
- Record passenger and seat information in the database

### Administrator workflow

The existing PHP application provides administrator workflows to:

- Log in to the administration area
- View active routes
- Add routes with operating dates and prices
- Edit route prices
- Delete routes
- View the flight manifest
- Open flight details, including passenger and seat information
- View route and passenger data used to calculate flight revenue

The administrator account is `rmiller` / `rmiller`. The PHP controller authorizes this account by username. The database also retains the historical `login` table for compatibility.

## Screenshots

These existing captures document the original customer and administration workflows.

### Customer workflow

| Screen | Description |
|---|---|
| ![Home screen](assets/Images/homescreen.jpg) | Guest home screen with route, ticket, and date search |
| ![Login](assets/Images/login.jpg) | Customer login form |
| ![Registration](assets/Images/register.jpg) | Customer registration form |
| ![Logged in](assets/Images/logged-in.jpg) | Search screen for a logged-in customer |
| ![Search](assets/Images/search.jpg) | Route search |
| ![Search results](assets/Images/search-results.jpg) | Available flight results |
| ![Availability](assets/Images/availability.jpg) | Seat availability |
| ![Booked seats](assets/Images/seats-booked.jpg) | Seat allocation after booking |
| ![Booked seats PNG](assets/Images/seats-booked.png) | Additional seat-allocation capture |
| ![Purchase complete](assets/Images/purchased.jpg) | Completed purchase screen |

### Administration workflow

| Screen | Description |
|---|---|
| ![Edit routes](assets/Images/admin-view-edit-routes.jpg) | Administrator route list and editing options |
| ![Add routes](assets/Images/admin-add-routes.jpg) | Add-route form |
| ![Add route](admi-add-route.jpg) | Additional add-route capture |
| ![Flight manifest](assets/Images/admi-view-mainfest.jpg) | Flight manifest and administration view |

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
5. Test guest route search, registration/login, cart seat selection, passenger submission, and the administrator route/manifest pages.

Use the fictional accounts in the SQL data for testing. The suspended sample user is `eoinnolan`; the current PHP authentication method does not enforce the `suspend` flag, so suspension is stored data rather than a complete application feature.

The local PHP configuration in [`library.php`](library.php) uses host `127.0.0.1`, port `3307`, database `airline2026`, user `root`, and the configured local password. Check the local configuration on your own machine; development credentials must not be used in production.

## Project structure

- [`controller.php`](controller.php) — central GET/POST command router and request handlers
- [`library.php`](library.php) — view classes, cart behavior, seat processing, and `DBManager`
- [`airline2026.sql`](airline2026.sql) — standalone MySQL 5.7 database schema and test data
- [`mystyles.css`](mystyles.css) — customer-facing styles
- [`insidestyles.css`](insidestyles.css) — internal and administration styles
- [`assets/Images/`](assets/Images/) — existing application screenshots
- [`old/airline2026_backup.sql`](old/airline2026_backup.sql) — historical backup/reference only

The original interface uses a fixed-width layout of approximately 800 pixels and traditional CSS floats rather than a modern responsive framework. `controller.php` dispatches requests to the views and handlers in `library.php`; supporting classes handle page presentation, cart operations, seat processing, and database access.

## Planned booking enhancements

The database now provides `booking`, `booking_flights`, and dated `flights` structures that can support future itinerary work. Potential future enhancements include:

- Return journeys with separate outbound and return flights
- Multi-city itineraries with ordered flight legs
- Grouping several selected flights into one booking
- Consistent flight search, availability, cart, and booking-completion logic
- A three-pane search, available-flights, and selected-itinerary interface
- More robust validation and parameterized database access

These are planned enhancements, not claims about functionality currently implemented by the PHP source. The current PHP code still searches routes, uses the session cart, displays 20 seat positions, and writes legacy `passangers` records.

## Backup and limitations

[`old/airline2026_backup.sql`](old/airline2026_backup.sql) is a retained historical backup/reference dump. It is not required for installation and must not be run in sequence with the standalone script.

Known limitations include:

- The active cart UI displays only 20 zero-based seat positions.
- PHP does not yet render the full 180/220-seat aircraft map or query the `flights` table during search.
- Passwords are stored and compared in plain text for legacy compatibility.
- Several PHP queries use string interpolation rather than prepared statements.
- The `suspend` field is stored but not enforced by the current authentication method.
- Payment details are fictional test data; there is no production payment processing.
- The project is not a production-ready airline operations or booking system.

Before real-world use, the application would require a security and reliability review, secure credential handling, parameterized queries, protection of customer/payment data, and modernization of legacy PHP/database conventions.
