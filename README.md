# Airline2026

Airline2026 is a restoration and development project based on an original 2014 PHP airline booking application. It provides customer booking and administrator route-management workflows backed by a MySQL database.

The goal is to preserve the original PHP architecture, MVC structure, and interface while restoring functionality, improving code clarity, and introducing carefully scoped enhancements. The application is being extended incrementally rather than rewritten.

Originally developed as part of the **Higher Diploma in Cloud and Mobile with Data Science**, the project reflects the web development practices and conventions of its time.

| Screen | Description |
|---|---|
| ![Home screen](assets/Images/newHome.jpg) | Guest home screen with route, ticket, and date search |

## Technologies

The project uses the following technologies:

- PHP
- MySQL
- PDO for database access
- HTML forms and server-rendered views
- CSS (`mystyles.css` and `insidestyles.css`)
- PHP sessions and cookies for cart and login state

The application is primarily server-rendered PHP and HTML. JavaScript may be introduced or extended where it improves interactions such as seat selection and itinerary management, without replacing the existing server-side architecture.

The supplied SQL file preserves the historical schema and sample data. Its original schema reflects the PHP and MySQL conventions of the 2014 project; the current local development environment uses newer software versions.

## Features

### Existing customer booking workflow

The original application includes the following functionality:

- Search routes by origin, destination, travel date, and number of tickets.
- Display matching route and travel-date results.
- Work with the application's existing 20-seat flight model.
- Select seats and add a provisional flight to the session-backed cart.
- Review, update, or remove cart items.
- Register a customer account or continue as a guest.
- Enter passenger, passport, email, and payment details.
- Record passenger and seat information in the database.
- Log in and log out.

These features describe the existing application structure; individual workflows remain subject to testing and correction as part of the restoration.

### Existing administration workflow

- Log in to the administration area.
- View active routes.
- Add routes with operating dates and prices.
- Edit route prices.
- Delete routes.
- View a flight manifest.
- Open flight details, including passenger and seat information.
- View route and passenger data used to calculate flight revenue.

### Proposed booking-system enhancements

The next development stage will extend the booking model to support complete itineraries rather than treating each selected flight as an isolated cart item.

Planned enhancements include:

- Single-flight bookings using the existing workflow as the starting point.
- Return journeys with separate outbound and return travel dates.
- Multi-city itineraries containing several flights in a defined order.
- A booking record that groups the flights belonging to one itinerary.
- Improved consistency between flight search, seat availability, cart processing, and booking completion.
- A three-pane booking interface for search criteria, available flights, and the selected itinerary.
- Incremental improvements to validation and database access while preserving the existing MVC structure.

These enhancements are **proposed work**, not a claim that the functionality has already been implemented.

## Proposed Booking-System Extension

### Design principles

The database will be extended additively wherever practical. Existing tables, column names, and methods will be retained until their dependencies have been checked and any replacement has been tested.

The objective is to preserve working legacy functionality while providing a clearer structure for scheduled flights and multi-leg bookings.

### Proposed database structure

| Table | Purpose | Planned approach |
|---|---|---|
| `routes` | Defines a route between two locations, including operating dates and price. | Retain the existing table and its current role. |
| `passangers` | Stores passenger, travel, and seat information. The spelling is retained from the original schema. | Preserve existing columns initially; review how passenger records will link to bookings. |
| `user` | Stores customer account information. | Retain the existing table initially. |
| `login` | Supports the historical login-related schema. | Inspect its current dependencies before changing it. |
| `flights` | Represents an individual scheduled departure associated with a route and travel date. | Proposed new table. |
| `booking` | Represents a booking as a whole, with a unique booking identifier. | Proposed new table. |
| `booking_flights` | Associates individual flights with a booking and records their itinerary order. | Proposed new table. |

The precise columns, keys, constraints, and relationships will be finalised after reviewing the existing database and `Cart` implementation.

### How the proposed model supports itineraries

A return journey would be represented as one booking containing two flights. A multi-city journey could contain three, six, or more flights without requiring separate outbound and return columns for every possible leg.

For example, the conceptual structure could be:

**`booking`**

| booking_id | passenger_id |
|---:|---:|
| 101 | 25 |

**`booking_flights`**

| booking_id | flight_id | sequence |
|---:|---:|---:|
| 101 | 501 | 1 |
| 101 | 608 | 2 |

In this example, booking `101` contains two flights, in the order shown. The flight identifiers are illustrative, not existing records from the database.

The final design must also account for passenger counts, individual seat assignments, booking status, and the relationship between passengers and bookings. Group bookings may require an explicit booking-to-passenger relationship rather than assuming that every booking has exactly one passenger.

### Routes and scheduled flights

The existing `routes` table describes route definitions and their operating-date ranges. It should not be confused with an individual scheduled departure.

The proposed `flights` table would distinguish individual departures on specific dates. This provides a foundation for selecting outbound, return, and connecting flights independently.

The existing 20-seat model and passenger records must be reviewed before introducing a new capacity or availability mechanism. The extension should avoid maintaining conflicting versions of seat availability.

### Database migration strategy

The original SQL setup file, `airline2013.sql`, contains the historical schema and sample data.

Future database changes should be introduced through a **separate migration script** that adds the new tables and any necessary columns. The original setup script must not be rerun against a database containing development data unless it has been reviewed and the data has been backed up, because it may drop and recreate existing tables.

The migration process will be:

1. Review the current schema, foreign-key relationships, and database access methods.
2. Define the new tables and relationships.
3. Add the new structures through an additive SQL migration.
4. Update `DBManager` to support the new booking model.
5. Adapt `Cart` to group selected flights into a booking.
6. Test the existing single-flight workflow.
7. Test return journeys and multi-city itineraries.
8. Verify seat availability, booking records, and passenger details against the database.

Existing tables and methods should only be changed when necessary, with their callers and dependencies checked first.

## Proposed Booking Interface

The booking interface is intended to build on the existing views and CSS rather than replace them.

The proposed middle section will use three panes:

1. **Search criteria:** origin, destination, travel date, passenger count, and journey type.
2. **Available flights:** matching departures for the current search or itinerary leg.
3. **Selected itinerary:** chosen flights in order, with dates, seat selections, and a running total.

The layout should accommodate a single flight, a return journey, and a multi-city itinerary. Existing public and authenticated view structures will be retained wherever practical, with new styles scoped to the booking layout to minimise unintended effects on legacy pages.

## Screenshots

The screenshots are existing captures of the original application. They document the customer booking flow and administrative screens without implying that the proposed multi-flight booking enhancements are already complete.

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

## Running locally

The application is a traditional PHP application with `controller.php` as its entry point.

1. Install a local PHP runtime with the PDO MySQL extension and a MySQL server.
2. Create the local database and import [`airline2013.sql`](airline2013.sql) into the appropriate database.
3. Check the database host, port, username, password, and database name configured in `DBManager` in [`library.php`](library.php). Use credentials appropriate for the local environment rather than copying credentials from the historical source.
4. Serve the repository from a PHP-enabled web server.
5. Open `controller.php` in a browser.

For the current local development environment, the PHP development server can be started from PowerShell in the project directory:

```powershell
& "C:\php-8.4.26\php.exe" -S localhost:8000
```

Open the application at:

[http://localhost:8000/controller.php](http://localhost:8000/controller.php)

The current local MySQL environment uses host `127.0.0.1` and port `3307`. The database name should match the one configured in `DBManager`; the local development database is currently named `airline2026`.

The historical SQL file is named `airline2013.sql`, even though the restored project is called Airline2026. The SQL file name and the local database name are separate things.

The original schema includes the `login`, `user`, `routes`, and `passangers` tables. The spelling `passangers` is retained from the original source and schema.

## Project structure

- [`controller.php`](controller.php) — central GET/POST command router and request handlers.
- [`library.php`](library.php) — view classes, cart behaviour, seat processing, and `DBManager`.
- [`airline2013.sql`](airline2013.sql) — historical MySQL schema and sample data.
- [`mystyles.css`](mystyles.css) — customer-facing styles.
- [`insidestyles.css`](insidestyles.css) — internal and administration styles.
- [`assets/Images/`](assets/Images/) — existing application screenshots.

The original interface uses a fixed-width layout of approximately 800 pixels and traditional CSS floats rather than a modern responsive framework. The source also contains historical naming and implementation patterns from the 2014 project.

The central controller dispatches requests to the relevant application logic and views. Supporting classes handle page presentation, cart operations, seat processing, and database access. This original structure is retained as part of the restoration.

## Naming Register

The original 2014 application was developed under time constraints, and some class, method, and controller command names reflect the naming conventions and experience of the time. As part of this restoration, existing names are being documented alongside clearer alternatives.

The principle is to preserve the working application and its original MVC structure. Classes and functions are renamed incrementally, with their references checked and affected workflows tested. This is a controlled improvement to the existing code, not a redesign.

The register distinguishes proposed names from changes that have been implemented and syntax-checked. Browser workflow testing remains environment-dependent.

### 1. View classes

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

The proposed names describe each class's purpose more clearly. Class names follow the PascalCase convention. Existing class names remain in the register so the refactoring history is documented.

### 2. Controller commands

Controller commands are values submitted through URLs, links, or HTML forms. The `dispatchRequest()` function in `controller.php` reads the `cmd` value and dispatches the request through a `switch` statement.

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

### 3. Controller handler functions

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
| `handleprocessCart()` | `handleCartProcessing()` | Implemented rename; existing `DBManager::processCart()` call remains unresolved and was not altered |
| `handledisplayFlightdetails()` | `handleFlightDetailsRequest()` | Implemented; retrieves flight and passenger details |

`isValidDate()` remains unused, as was the original function. `handleCartProcessing()` still calls the pre-existing `DBManager::processCart()` name; no method was invented or changed because that method is absent from the active `DBManager` implementation.

### 4. `DBManager` methods

| Original method | Clearer name | Status |
|---|---|---|
| `processLogin()` | `authenticateUser()` | Implemented; syntax-checked |
| `addUser()` | `registerUser()` | Implemented; syntax-checked |
| `updateRoute()` | `updateRoutePrice()` | Implemented; syntax-checked |
| `checkSeats()` | `hasAvailableSeats()` | Deferred — implementation returns `true` unconditionally |
| `getAllRoutes()` | `getAllRoutes()` | Keep |
| `getRoute()` | `getRouteById()` | Implemented; syntax-checked |
| `loadRoutes()` | `insertSampleRoutes()` | Implemented; inserts the predefined sample routes |
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

Some proposed names are deliberately descriptive and may be shortened after their responsibilities and call sites have been checked. Names should reflect what methods actually do, rather than what we assume from their original names.

### 5. `Cart` methods

| Original method | Clearer name | Status |
|---|---|---|
| `addToCart()` | `addFlight()` | Implemented; syntax-checked |
| `deleteFromCart()` | `removeFlight()` | Implemented; syntax-checked |
| `updateTransactions()` | `recordBookings()` | Implemented; records selected cart seats |
| `displayCart()` | `displayCart()` | Keep |
| `processFromCart()` | `processSeatSelections()` | Implemented; syntax-checked |

### 6. Naming conventions

The following conventions guide future changes:

- **Classes:** PascalCase, for example `FlightSearchView` and `RegistrationView`.
- **Methods and functions:** camelCase, for example `getRouteById()` and `recordPassengerBooking()`.
- **Controller commands:** descriptive camelCase values, for example `searchFlights` and `showCheckout`.
- **Database methods:** names that describe the operation and the object being acted on.
- **Legacy names:** retain original names in this register with their final status.

Names belonging to different application layers should remain distinguishable. For example, a controller command, a controller handler, and a `DBManager` method are related but separate elements of the application. Their replacements should reflect their respective responsibilities.

### 7. Renaming process

Changes will be made in small, related groups:

1. Document the existing classes, methods, controller commands, and handlers.
2. Check each proposed name against the actual implementation and its callers.
3. Confirm that the replacement accurately describes the code's behaviour.
4. Rename one related group at a time.
5. Search for references to the original names and update the relevant links, forms, dispatch conditions, and method calls.
6. Test the affected workflow.
7. Mark a change as implemented or tested only after verifying it.

The register provides a record of the original application's design and the completed naming refactor while preserving its existing functionality and MVC structure.

Archived files under [`old/`](old/) were not renamed because they are historical snapshots and are not loaded by the active `controller.php` entry point. The active implementation also contains a commented historical `Cart` example; it is non-executable reference text.

## Legacy status and future work

This is a historical and educational application, not a production-ready booking system.

The immediate objective is to preserve the original application, document its functionality, and establish a working local reference. The next stage is to extend its booking model carefully, starting with database design and the existing cart and seat-processing code.

Before any real-world use, the application would require a security and reliability review, including replacing plain-text credential handling, parameterising database queries, protecting sensitive customer and payment data, and updating legacy PHP/database conventions.

Future changes should be treated as deliberate, testable stages rather than a replacement for the original project. The guiding principle is to **preserve what works, document what changes, and extend the system without unnecessarily disturbing its original architecture**.
