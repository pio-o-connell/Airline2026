# Airline2026

Airline2026 is a restoration and development project based on an original 2014 PHP airline booking application. It provides customer booking and administrator route-management workflows backed by a MySQL database.

The goal is to preserve the original PHP architecture, MVC structure, and interface while restoring functionality, improving code clarity, and introducing carefully scoped enhancements. The application is being extended incrementally rather than rewritten.

Originally developed as part of the **Higher Diploma in Cloud and Mobile with Data Science**, the project reflects the web development practices and conventions of its time.

| Screen | Description |
|---|---|
| ![Home screen](assets/Images/newHome.jpg) | Guest home screen with route, ticket, and date search |

## Technologies

- PHP, using server-rendered HTML views
- MySQL
- PDO for database access
- HTML forms
- CSS (`mystyles.css` and `insidestyles.css`)
- PHP sessions and cookies for cart and login state
- JavaScript for interactive features where appropriate

The application retains its legacy PHP/MVC structure. The current local development environment uses PHP 8.4.26 and MySQL on `127.0.0.1:3307`. The local database is named `airline2026`.

## Current Database

The database has been extended beyond the original route-and-passenger model. The current setup script is `airline2026_with_aircraft_and_test_data.sql`, which has been imported successfully into the local `airline2026` database.

The database contains nine tables:

| Table | Purpose |
|---|---|
| `login` | Legacy login-related records |
| `user` | Customer account records |
| `routes` | Route definitions, operating dates, origin, destination and price |
| `flights` | Individual scheduled flights, including flight code, date, aircraft, capacity and timing fields |
| `aircraft` | Aircraft configurations and seating capacities |
| `aircraft_seats` | Individual seat numbers, labels and premium-seat designations |
| `passangers` | Legacy passenger and seat-booking records; the original spelling is retained |
| `booking` | Booking-level records and status |
| `booking_flights` | Links bookings to flights and records itinerary order |

The imported test data currently contains:

- 12 routes
- 14 user records
- 3 login records
- 2 aircraft configurations
- 400 configured aircraft seats
- 2,788 flight records
- 34 passenger records
- 15 bookings
- 15 booking-to-flight links

These figures describe the current local test database and are not intended as production data.

### Aircraft and Seat Configuration

Two aircraft configurations are included:

| Aircraft code | Configured capacity | Premium seats |
|---|---:|---:|
| `AC180` | 180 | 18 |
| `AC220` | 220 | 18 |

The `aircraft_seats` table defines the seat layout for each aircraft, including seat numbers, labels and premium-seat markers.

Seat availability is flight-specific. A seat may be available on one flight and occupied on another, even when both flights use the same aircraft configuration. Availability must therefore be checked against the selected flight and its passenger bookings.

The intended seat-selection interface will use individual clickable HTML/CSS elements rather than hotspots over a static aircraft image. JavaScript will manage the selection interaction, while PHP and MySQL will validate availability and persist bookings.

The legacy `passangers` table and its existing constraints have been retained for compatibility while the PHP dependencies are reviewed. Its current route/date/seat uniqueness constraint must be reviewed before implementing flight-specific duplicate-seat prevention.

### Flight Information

The extended `flights` table introduces individual scheduled departures associated with a route and travel date.

The new schema includes fields for:

- Flight code
- Route and flight date
- Aircraft assignment
- Seat capacity
- Scheduled departure and arrival times
- Expected departure and arrival times
- Flight price and status

The demonstration data includes generated flight records. The current timings and flight codes are test data and must be checked before being treated as real schedules.

The new schema has been imported successfully. The existing web page still loads, but this confirms only basic application/database connectivity. Flight searches, booking operations, administration workflows and seat availability still need to be tested against the extended database.

## Existing Application Features

The original application includes the following customer and administrator workflows. Their individual behaviour remains subject to testing against the updated schema.

### Customer Workflow

- Register a customer account or log in.
- Search routes by origin, destination, travel date and number of tickets.
- Display matching route and travel-date results.
- Work with the original 20-seat flight model in the legacy implementation.
- Select seats and add provisional flights to the session-backed cart.
- Review, update or remove cart items.
- Enter passenger, passport, email and payment details.
- Record passenger and seat information in the database.
- Log out.

The original 20-seat model is part of the legacy implementation. The extended database now contains aircraft configurations with capacities of 180 and 220 seats. The PHP booking workflow must be adapted and tested to use the new configuration correctly.

### Administration Workflow

The original application includes administration functions for:

- Viewing active routes.
- Adding routes with operating dates and prices.
- Editing route prices.
- Deleting routes.
- Viewing flight manifests.
- Opening flight details, including passenger and seat information.
- Viewing route and passenger information used to calculate flight revenue.

Aircraft configuration and seat administration will be considered as part of the next development stages.

## Development Plan

The application will be extended incrementally, starting with the existing home page.

### 1. Home Page

The first task is to inspect the existing home-page view, controller routing, database calls and CSS dependencies.

The aim is to understand the current implementation before making changes. The existing header, menu, footer, fixed-width layout and general Airline2014 character should be preserved wherever practical.

The home page will provide the starting point for integrating the new flight information.

### 2. Flight Information

The next step is to check how the application retrieves and displays routes and scheduled flights.

Where appropriate, the interface will be extended to display flight codes, dates, departure and arrival times, and prices using the new database fields.

Existing controller, view and database methods will be reviewed before they are changed.

### 3. Three-Pane Booking Interface

The proposed booking interface will build on the existing views and CSS rather than replace them.

The central transactional area will contain three panes:

1. **Search criteria:** origin, destination, travel date, passenger count and journey type.
2. **Available flights:** matching departures for the current search or itinerary leg.
3. **Selected itinerary:** selected flights in order, with dates, seat selections and a running total.

The layout should support single-flight bookings, return journeys and multi-city itineraries as the booking workflow is extended.

The original public and authenticated view structures will be retained wherever practical. New styles will be scoped to the relevant booking layout to minimise unintended effects on legacy pages.

### 4. Interactive Seat Map

The seat-selection interface will represent the aircraft from an overhead perspective, using individual clickable HTML/CSS seat elements.

Seat states will distinguish between:

- Available seats
- The currently selected seat
- Unavailable or occupied seats
- Premium seats

JavaScript will handle the interactive selection and update the booking pane. PHP and MySQL will validate the selected flight and seat before confirming a booking.

The server must prevent duplicate seat bookings, regardless of what the browser displays.

### 5. Booking and Itinerary Improvements

Once the existing single-flight workflow has been reviewed and tested, the booking model can be extended to support complete itineraries.

Planned enhancements include:

- Grouping multiple flights into one booking.
- Supporting separate outbound and return dates.
- Supporting multi-city itineraries with flights in a defined order.
- Maintaining consistent seat availability between search results, the cart and booking completion.
- Recording booking status and passenger relationships accurately.

These are planned enhancements, not claims that the full functionality is already implemented.

## Database Design and Migration

The original SQL setup file, `airline2013.sql`, contains the historical schema and sample data. The current extended setup is `airline2026_with_aircraft_and_test_data.sql`.

The extended setup script is intended for a fresh test setup. It drops and recreates tables before inserting the schema and demonstration data.

**Do not rerun the extended setup script against a database containing data you need to keep unless you have taken and checked a backup.**

The current development database is named `airline2026`, hosted locally at `127.0.0.1:3307`.

The database structure will be refined only after the existing PHP implementation and its dependencies have been reviewed. Existing table names, columns and methods should be retained wherever practical. Changes to keys, constraints and relationships must be checked against the code that reads and writes the affected tables.

The development process will be:

1. Inspect the home-page view, controller and database calls.
2. Review the existing `DBManager`, `Cart` and seat-processing methods.
3. Identify the minimum changes required to use the new flight and aircraft data.
4. Adapt the existing PHP code in small, testable steps.
5. Test the current customer and administrator workflows.
6. Implement and test flight-specific seat availability.
7. Extend the booking workflow only when the existing single-flight path is understood.

The existing database connection remains the same when the tables are recreated; the schema and data inside the database are what have changed.

## Screenshots

The following screenshots document the existing application. They do not imply that the planned multi-flight booking and interactive seat-map enhancements have been completed.

### Customer Workflow

| Screen | Description |
|---|---|
| ![Home screen](assets/Images/homescreen.jpg) | Guest home screen with route, ticket and date search |
| ![Login](assets/Images/login.jpg) | Customer login form |
| ![Registration](assets/Images/register.jpg) | Customer registration form |
| ![Logged in](assets/Images/logged-in.jpg) | Search screen for a logged-in customer |
| ![Search](assets/Images/search.jpg) | Route search |
| ![Search results](assets/Images/search-results.jpg) | Available flight results |
| ![Availability](assets/Images/availability.jpg) | Seat availability |
| ![Booked seats](assets/Images/seats-booked.jpg) | Seat allocation after booking |
| ![Booked seats PNG](assets/Images/seats-booked.png) | Additional seat-allocation capture |
| ![Purchase complete](assets/Images/purchased.jpg) | Completed purchase screen |

### Administration Workflow

| Screen | Description |
|---|---|
| ![Edit routes](assets/Images/admin-view-edit-routes.jpg) | Administrator route list and editing options |
| ![Add routes](assets/Images/admin-add-routes.jpg) | Add-route form |
| ![Add route](admi-add-route.jpg) | Additional add-route capture |
| ![Flight manifest](assets/Images/admi-view-mainfest.jpg) | Flight manifest and administration view |

## Running Locally

The application uses `controller.php` as its entry point.

1. Install PHP with the PDO MySQL extension and a local MySQL server.
2. Create or select the local database `airline2026`.
3. Check the database host, port, username, password and database name configured in `DBManager` in `library.php`.
4. From PowerShell in the project directory, start the PHP development server:

   ```powershell
   & "C:\php-8.4.26\php.exe" -S localhost:8000
   ```

5. Open [http://localhost:8000/controller.php](http://localhost:8000/controller.php).

The current local MySQL environment uses host `127.0.0.1` and port `3307`. Use the credentials configured for the local development environment.

The historical SQL file is named `airline2013.sql`, even though the restored project is called Airline2026. The SQL filename and the local database name are separate things.

The original table spelling `passangers` is retained from the legacy source. It should not be renamed until all relevant PHP references and database dependencies have been checked.

## Project Structure

- [`controller.php`](controller.php) — central GET/POST command router and request handlers.
- [`library.php`](library.php) — view classes, cart behaviour, seat processing and `DBManager`.
- [`airline2013.sql`](airline2013.sql) — historical MySQL schema and sample data.
- [`airline2026_with_aircraft_and_test_data.sql`](airline2026_with_aircraft_and_test_data.sql) — extended schema and seeded test data for aircraft, seats, flights and bookings.
- [`mystyles.css`](mystyles.css) — customer-facing styles.
- [`insidestyles.css`](insidestyles.css) — internal and administration styles.
- [`assets/Images/`](assets/Images/) — existing application screenshots.

The original interface uses a fixed-width layout of approximately 800 pixels and traditional CSS floats rather than a modern responsive framework. The source also contains historical naming and implementation patterns from the 2014 project.

The central controller dispatches requests to the relevant application logic and views. Supporting classes handle page presentation, cart operations, seat processing and database access. This original structure is retained as part of the restoration.

## Naming Register

The original 2014 application was developed under time constraints, and some class, method and controller command names reflect the conventions and experience of the time. The restoration documents these names alongside clearer alternatives.

The principle is to preserve the working application and its original MVC structure. Classes and functions are renamed incrementally, with references checked and affected workflows tested. This is a controlled improvement to the existing code, not a redesign.

The register distinguishes proposed names from changes that have been implemented and syntax-checked. Browser workflow testing remains environment-dependent.

### 1. View Classes

| Original name | Clearer name | Status |
|---|---|---|
| `OutsideView` | `PublicBaseView` | Implemented; syntax-checked |
| `InsideView` | `AuthenticatedBaseView` | Implemented; used by administrator views |
| `loginView` | `LoginView` | Implemented; syntax-checked |
| `registerView` | `RegistrationView` | Implemented; syntax-checked |
| `adminView` | `AdminRoutesView` | Implemented; syntax-checked |
| `adminEditView` | `EditRouteView` | Implemented; syntax-checked |
| `adminAddRoute` | `AddRouteView` | Implemented; syntax-checked |
| `adminManifest` | `FlightManifestView` | Implemented; syntax-checked |
| `SearchView` | `FlightSearchView` | Implemented; syntax-checked |
| `ResultsView` | `FlightSearchResultsView` | Implemented; syntax-checked |
| `purchaseView` | `PassengerDetailsView` | Implemented; syntax-checked |
| `CheckoutView` | `CheckoutView` | Keep — already clear |
| `Cart` | `Cart` | Keep — already clear |
| `DBManager` | `DBManager` | Keep — already clear |

### 2. Controller Commands

Controller commands are values submitted through URLs, links or HTML forms. The `dispatchRequest()` function in `controller.php` reads the `cmd` value and dispatches the request through a `switch` statement.

| Original command | Clearer command | Status |
|---|---|---|
| Empty command (`""`) | `showHome` or retain the default route | Review |
| `login` | `showLogin` | Implemented; syntax-checked |
| `register` | `showRegistration` | Implemented; syntax-checked |
| `loginRequest` | `authenticateUser` | Implemented; syntax-checked |
| `registerRequest` | `registerUser` | Implemented; syntax-checked |
| `search` | `searchFlights` | Implemented; syntax-checked |
| `addToCart` | `addFlightToCart` | Implemented; syntax-checked |
| `Update` | `updateCartSeats` | Implemented; syntax-checked |
| `checkoutView` | `showCheckout` | Implemented; syntax-checked |
| `customerRequest` | `submitPassengerDetails` | Implemented; syntax-checked |
| `delCheckoutItem` | `removeCartItem` | Implemented; syntax-checked |
| `purchase` | `showPassengerDetails` | Implemented; syntax-checked |
| `editroute` | `showEditRoute` | Implemented; syntax-checked |
| `amendroute` | `updateRoute` | Implemented; syntax-checked |
| `deleteRoutes` | `deleteSelectedRoutes` | Implemented; syntax-checked |
| `addRoutes` | `showAddRoute` | Implemented; syntax-checked |
| `addRoute` | `createRoute` | Implemented; syntax-checked |
| `manifest` | `showFlightManifest` | Implemented; syntax-checked |
| `openflight` | `showFlightDetails` | Implemented; syntax-checked |
| `defaultAdmin` | `showAdminDashboard` | Implemented; syntax-checked |
| `viewdate` | `showFlightDate` | Deferred — renders results and processes seating |
| `logout` | `logout` | Keep — already clear |

The empty command selects a default view based on the user's cookie and remains unchanged. The `viewdate` command performs more than one action, so its proposed replacement was deferred rather than forcing a narrower name.

### 3. Controller Handler Functions

A controller command and the PHP function that processes it are separate elements. Their names are recorded separately where they differ.

| Original function | Clearer name | Responsibility |
|---|---|---|
| `main()` | `dispatchRequest()` | Implemented; reads the command and selects the appropriate action |
| `validateDate()` | `isValidDate()` | Implemented; validates a date against a specified format |
| `handleSearch()` | `handleFlightSearch()` | Implemented; validates search inputs and displays flight results |
| `handleAddToCart()` | `handleAddFlightToCart()` | Implemented; checks seat availability and adds a provisional flight |
| `handleAdduser()` | `handleUserRegistration()` | Implemented; registers a user through `DBManager` |
| `handleUpdateprice()` | `handleRoutePriceUpdate()` | Implemented; updates a route's price |
| `handleDeleteroutes()` | `handleRouteDeletion()` | Implemented; deletes selected routes |
| `handleAddNewRoute()` | `handleRouteCreation()` | Implemented; validates operating dates and adds a route |
| `handleAddtransation()` | `handlePassengerDetailsSubmission()` | Implemented; saves submitted customer details to cart transaction data |
| `handleViewManifest()` | `handleFlightManifestRequest()` | Implemented; displays the administrator's flight manifest |
| `handleLogin()` | `handleUserAuthentication()` | Implemented; authenticates the user and displays the appropriate view |
| `handleSeating()` | `handleSeatOccupancyRequest()` | Implemented; requests seat occupancy information |
| `handleprocessCart()` | `handleCartProcessing()` | Implemented rename; existing `DBManager::processCart()` call remains unresolved |
| `handledisplayFlightdetails()` | `handleFlightDetailsRequest()` | Implemented; retrieves flight and passenger details |

`isValidDate()` remains unused, as was the original function. `handleCartProcessing()` still calls the pre-existing `DBManager::processCart()` name; no method was invented or changed because that method is absent from the active `DBManager` implementation.

### 4. `DBManager` Methods

| Original method | Clearer name | Status |
|---|---|---|
| `processLogin()` | `authenticateUser()` | Implemented; syntax-checked |
| `addUser()` | `registerUser()` | Implemented; syntax-checked |
| `updateRoute()` | `updateRoutePrice()` | Implemented; syntax-checked |
| `checkSeats()` | `hasAvailableSeats()` | Deferred — implementation returns `true` unconditionally |
| `getAllRoutes()` | `getAllRoutes()` | Keep |
| `getRoute()` | `getRouteById()` | Implemented; syntax-checked |
| `loadRoutes()` | `insertSampleRoutes()` | Implemented; inserts predefined sample routes |
| `displayRoutes()` | `displayRouteManagementList()` | Implemented; syntax-checked |
| `displayStaticRoutes()` | `displayRouteDetails()` | Implemented; syntax-checked |
| `displayActiveRoutes()` | `displayBookedFlights()` | Implemented; lists booked route/date combinations |
| `displayEditRoutes()` | `displayRouteForEditing()` | Implemented; syntax-checked |
| `deleteRoutes()` | `deleteSelectedRoutes()` | Implemented; syntax-checked |
| `addRoute()` | `createRoute()` | Implemented; syntax-checked |
| `checkNmrroutes()` | `countRoutes()` | Implemented; syntax-checked |
| `handleretrieveCustomerdetails()` | `displaySavedCustomerDetails()` | Implemented; syntax-checked |
| `processTransaction()` | `recordPassengerBooking()` | Implemented; records passenger and seat data |
| `processSeating()` | `displaySeatOccupancy()` | Implemented; syntax-checked |
| `processSeating1()` | `calculateAvailableSeats()` | Implemented; syntax-checked |
| `processdisplayFlightdetails()` | `displayFlightManifestDetails()` | Implemented; syntax-checked |

Some proposed names are deliberately descriptive and may be shortened after their responsibilities and call sites have been checked. Names should reflect what methods actually do.

### 5. `Cart` Methods

| Original method | Clearer name | Status |
|---|---|---|
| `addToCart()` | `addFlight()` | Implemented; syntax-checked |
| `deleteFromCart()` | `removeFlight()` | Implemented; syntax-checked |
| `updateTransactions()` | `recordBookings()` | Implemented; records selected cart seats |
| `displayCart()` | `displayCart()` | Keep |
| `processFromCart()` | `processSeatSelections()` | Implemented; syntax-checked |

### 6. Naming Conventions

- **Classes:** PascalCase, for example `FlightSearchView` and `RegistrationView`.
- **Methods and functions:** camelCase, for example `getRouteById()` and `recordPassengerBooking()`.
- **Controller commands:** descriptive camelCase values, for example `searchFlights` and `showCheckout`.
- **Database methods:** names that describe the operation and the object being acted on.
- **Legacy names:** retain original names in this register with their final status.

Names belonging to different application layers should remain distinguishable. A controller command, controller handler and `DBManager` method are related but separate elements.

### 7. Renaming Process

Changes will be made in small, related groups:

1. Document existing classes, methods, controller commands and handlers.
2. Check each proposed name against the actual implementation and its callers.
3. Confirm that the replacement accurately describes the code's behaviour.
4. Rename one related group at a time.
5. Search for references to original names and update relevant links, forms, dispatch conditions and method calls.
6. Test the affected workflow.
7. Mark a change as implemented or tested only after verifying it.

Archived files under [`old/`](old/) were not renamed because they are historical snapshots and are not loaded by the active `controller.php` entry point. The active implementation also contains a commented historical `Cart` example; it is non-executable reference text.

## Legacy Status and Future Work

Airline2026 is a historical and educational application, not a production-ready booking system.

The immediate objective is to preserve the original application, document its functionality, and establish a working local reference. The next stage begins with the home page and its existing controller, view, CSS and database dependencies.

Before real-world use, the application would require a security and reliability review, including replacing plain-text credential handling, parameterising database queries, protecting sensitive customer and payment data, and updating legacy PHP/database conventions.

Future changes should be deliberate, testable stages rather than a replacement for the original project. The guiding principle is to **preserve what works, document what changes, and extend the system without unnecessarily disturbing its original architecture**.
