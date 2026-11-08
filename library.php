<?php

    const AIRLINE_NAME = 'PHP Airlines';
    const AIRLINE_LOGO = 'assets/Images/logo.png';   // skipped if missing
    const CURRENCY     = '€';

    const AIRPORT_TIMEZONES = array(
        'dublin' => 'Europe/Dublin',       'cork' => 'Europe/Dublin',
        'shannon' => 'Europe/Dublin',      'knock' => 'Europe/Dublin',
        'heathrow' => 'Europe/London',     'manchester' => 'Europe/London',
        'edinburgh' => 'Europe/London',
        'amsterdam' => 'Europe/Amsterdam', 'berlin' => 'Europe/Berlin',
        'brussels' => 'Europe/Brussels',
        'charles de gaulle' => 'Europe/Paris',
        'barcelona' => 'Europe/Madrid',    'rome' => 'Europe/Rome',
        'lisbon' => 'Europe/Lisbon',       'faro' => 'Europe/Lisbon',
    );

    function esc($v) {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }



    /**
     * Get the current command from the request.
     *
     * @return string
     */
    function airlineCurrentCommand() {
        return $_GET['cmd'] ?? $_POST['cmd'] ?? '';
    }

    function airlineCartHasFlights() {
        $cart = $_SESSION['cart'] ?? null;
        return $cart instanceof Cart && !empty($cart->getCartElements());
    }

    function airlineSeatsSelected() {
        $cart = $_SESSION['cart'] ?? null;
        if (!($cart instanceof Cart) || empty($cart->getCartElements())) {
            return false;
        }

        foreach ($cart->getCartElements() as $flight) {
            $seats = $flight['seatSelections'] ?? array();
            if (count($seats) !== (int)$flight['seatCount']) {
                return false;
            }
        }

        return true;
    }

    function renderAirlineNavLink($url, $label, $commands, $enabled = true) {
        if (!$enabled) {
            echo '<span class="nav-disabled" aria-disabled="true">' . esc($label) . '</span>';
            return;
        }

        $active = in_array(airlineCurrentCommand(), $commands, true);

        echo '<a href="' . esc($url) . '"';

        if ($active) {
            echo ' class="active" aria-current="page"';
        }

        echo '>' . esc($label) . '</a>';
    }


    class StripeGateway {

        private $secretKey;

        public function __construct() {
            $this->secretKey = getenv('STRIPE_SECRET_KEY');
        }

        public function createCheckoutSession(Cart $cart, $successUrl, $cancelUrl) {
            if (!$this->secretKey) {
                throw new RuntimeException('Stripe payment is temporarily unavailable.');
            }

            $fields = array(
                'mode' => 'payment',
                'success_url' => $successUrl . '&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $cancelUrl,
                'payment_method_types[0]' => 'card',
            );

            $line = 0;
            foreach ($cart->getCartElements() as $flight) {
                $fields['line_items[' . $line . '][price_data][currency]'] = 'eur';
                $fields['line_items[' . $line . '][price_data][product_data][name]'] =
                    'PHP Airlines ' . ($flight['flight_code'] ?? 'flight');
                $fields['line_items[' . $line . '][price_data][unit_amount]'] =
                    (int)round((float)$flight['unitFare'] * 100);
                $fields['line_items[' . $line . '][quantity]'] =
                    (int)$flight['seatCount'];
                $line++;
            }

            $response = $this->request('POST', '/v1/checkout/sessions', $fields);

            if (empty($response['id']) || empty($response['url'])) {
                throw new RuntimeException('Stripe did not return a checkout session.');
            }

            return $response;
        }

        public function retrieveCheckoutSession($sessionId) {
            if (!$this->secretKey) {
                throw new RuntimeException('Stripe payment is temporarily unavailable.');
            }

            if (!preg_match('/^cs_[A-Za-z0-9_]+$/', $sessionId)) {
                throw new InvalidArgumentException('Invalid Stripe checkout session.');
            }

            return $this->request('GET', '/v1/checkout/sessions/' . rawurlencode($sessionId));
        }

        private function request($method, $path, $fields = array()) {
            if (!function_exists('curl_init')) {
                throw new RuntimeException('Stripe payment is temporarily unavailable.');
            }

            $curl = curl_init('https://api.stripe.com' . $path);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $this->secretKey,
                'Content-Type: application/x-www-form-urlencoded'
            ));

            if ($method === 'POST') {
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($fields));
            }

            $body = curl_exec($curl);
            $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            curl_close($curl);

            if ($body === false || $error) {
                throw new RuntimeException('Stripe payment is temporarily unavailable.');
            }

            $response = json_decode($body, true);
            if ($status < 200 || $status >= 300 || !is_array($response)) {
                throw new RuntimeException('Stripe payment is temporarily unavailable.');
            }

            return $response;
        }
    }

    class FlashMessage{
        private const SESSION_KEY = '_flash_messages';

        private const TYPES = array(
            'success',
            'info',
            'warning',
            'error'
        );

        public static function add($type, $message)
        {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                return false;
            }

            if (!in_array($type, self::TYPES, true)) {
                $type = 'info';
            }

            if (!isset($_SESSION[self::SESSION_KEY])) {
                $_SESSION[self::SESSION_KEY] = array();
            }

            $_SESSION[self::SESSION_KEY][] = array(
                'type' => $type,
                'message' => (string)$message
            );

            return true;
        }

        public static function render()
        {
            if (
                session_status() !== PHP_SESSION_ACTIVE ||
                empty($_SESSION[self::SESSION_KEY])
            ) {
                return '';
            }

            $messages = $_SESSION[self::SESSION_KEY];

            // Flash messages are displayed only once.
            unset($_SESSION[self::SESSION_KEY]);

            $output = '<section class="flash-messages" aria-label="Notifications">';

            foreach ($messages as $message) {
                $type = in_array($message['type'], self::TYPES, true)
                    ? $message['type']
                    : 'info';

                $role = in_array($type, array('warning', 'error'), true)
                    ? 'alert'
                    : 'status';

                $output .= '<div class="flash-message flash-message--'
                    . $type . '" role="' . $role . '">';

                $output .= '<span class="flash-message__text">'
                    . htmlspecialchars($message['message'], ENT_QUOTES, 'UTF-8')
                    . '</span>';

                $output .= '<button type="button" class="flash-message__close"'
                    . ' aria-label="Dismiss notification">&times;</button>';

                $output .= '</div>';
            }

            $output .= '</section>';

            return $output;
        }
    }


    // Shared base view for public pages.
    abstract class PublicBaseView {
        // Shared public-page shell. Concrete views call inherited methods themselves.
        protected function top($info = false, $stylesheet = 'mystyles.css') {
            echo '
                    <!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>PHP Airlines</title>
                        <link rel="stylesheet" href="' . htmlspecialchars($stylesheet, ENT_QUOTES, 'UTF-8') . '">
                    </head>
                    <body class="public-page">
                        <div id="main" class="old">
                            <header id="header" class="old">
                                <h1>PHP Airlines.com</h1>
                                <p class="tagline">Your journey starts here</p>
                            </header>
                    ';
            echo FlashMessage::render();
            echo '
                <script>
                document.querySelectorAll(".flash-message__close").forEach(function(button) {
                    button.addEventListener("click", function() {
                        var message = this.closest(".flash-message");

                        if (message) {
                            message.remove();
                        }
                    });
                });
                </script>
                ';
        }

        public function menu($info = false) {
            $this->renderCustomerMenu(false);
        }

        // protected function renderCustomerMenu($loggedIn) {
        //     echo '<nav id="menu" class="old" aria-label="Main navigation">
        //         <div class="menu-left">
        //             <a href="controller.php">Home</a>
        //         </div>
        //         <div class="menu-right">
        //             <a href="controller.php?cmd=showCheckout">Cart</a>
        //             <a href="controller.php?cmd=showPassengerDetails">Checkout</a>';

        //     if ($loggedIn) {
        //         echo '<a href="controller.php?cmd=logout">Logout</a>';
        //     } else {
        //         echo '<a href="controller.php?cmd=showLogin">Login / Register</a>';
        //     }

        //     echo '</div></nav>';
        // }
        protected function renderCustomerMenu($loggedIn) {

            echo '<nav id="menu" class="old" aria-label="Main navigation">
                <div class="menu-left">';

            renderAirlineNavLink(
                'controller.php',
                'Home',
                array('', 'showHome', 'searchFlights', 'selectFlight', 'addFlightToCart')
            );

            echo '</div><div class="menu-right">';

            renderAirlineNavLink(
                'controller.php?cmd=showCheckout',
                'Cart',
                array('showCheckout', 'removeCartItem', 'showSeatSelection', 'updateCartSeats'),
                airlineCartHasFlights()
            );

            renderAirlineNavLink(
                'controller.php?cmd=showPassengerDetails',
                'Checkout',
                array(
                    'showPassengerDetails',
                    'submitPassengerDetails',
                    'startStripeCheckout',
                    'stripeSuccess',
                    'stripeCancel'
                ),
                airlineSeatsSelected()
            );

            if ($loggedIn) {
                renderAirlineNavLink(
                    'controller.php?cmd=logout',
                    'Logout',
                    array()
                );
            } else {
                renderAirlineNavLink(
                    'controller.php?cmd=showLogin',
                    'Login / Register',
                    array('showLogin', 'showRegistration', 'authenticateUser')
                );
            }

            echo '</div></nav>';
        }

        // protected function renderIdentity() {
        //     $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
        //     echo '<div id="identity">' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . '</div>';
        // }
        protected function renderIdentity() {
            $username = $_SESSION['username'] ?? 'Guest';

            $class = 'identity--guest';

            echo '<div id="identity" class="identity ' . $class . '">'
                . htmlspecialchars($username, ENT_QUOTES, 'UTF-8')
                . '</div>';
        }

        protected function bottom() {
            echo '
                        <footer id="footer" class="old">
                            <span>PHP Airlines.com</span>
                            <a href="controller.php">Public home</a>
                        </footer>
                    </div>
                </body>
                </html>
                ';
        }
    }

    /**
     * Shared flight search / results / details UI used by both
     * PublicHomeView (guest) and CustomerHomeView (logged-in).
     */
    trait FlightWorkspace {
    
        protected function renderWorkspace($showCheckout = false, $flights = null, $selectedId = null) {
            $selected = null;

            if (is_array($flights)) {
                foreach ($flights as $f) {
                    // if ($f['flight_id'] === $selectedId) {
                    if ((int)$f['flight_id'] === (int)$selectedId) {
                        $selected = $f;
                        break;
                    }
                }
            }
            echo '<div id="content" class="flight-workspace triple-pane">';

            // Pane 1 – Search form and results summary
            echo '<div class="workspace-search-column">';
            $this->searchPane($showCheckout);
            $this->resultsSummaryPane($flights);
            echo '</div>';

            // Pane 2 – Available flight cards
            $this->resultsPane($flights, $selectedId);

            // Pane 3 – Selected flight details and Add to Cart
            //$this->detailsPane($selected);
            if ($selected !== null) {
                $this->detailsPane($selected);
            } 
            echo '</div>';
        }

        protected function searchPane($showCheckout = false) {
            $routes   = (new DBManager())->getAllRoutes();
            $selRoute = isset($_SESSION['route']) ? $_SESSION['route'] : '';
            $tripType = isset($_SESSION['trip_type']) ? $_SESSION['trip_type'] : 'oneway';

            echo '<section class="search-card" aria-labelledby="search-title">';
            echo '<h2 id="search-title">Search flights</h2>';

            if ($showCheckout) {
                echo '<p><a href="controller.php?cmd=showCheckout">Go to checkout</a></p>';
            }

            // ===== TABS =====
            echo '<div class="trip-tabs" role="tablist">';
            echo '<button type="button" class="trip-tab' . ($tripType === 'oneway' ? ' is-active' : '') . '" data-trip="oneway">One Way</button>';
            echo '<button type="button" class="trip-tab' . ($tripType === 'roundtrip' ? ' is-active' : '') . '" data-trip="roundtrip">Round Trip</button>';
            echo '<button type="button" class="trip-tab' . ($tripType === 'multicity' ? ' is-active' : '') . '" data-trip="multicity">Multi-City</button>';
            echo '</div>';

            echo '<form action="controller.php" method="post">';
            echo '<input type="hidden" name="cmd" value="searchFlights">';
            echo '<input type="hidden" name="trip_type" id="trip_type_form" value="' . esc($tripType) . '">';

            echo '<div class="field-grid">';
            echo '<div class="field full"><label for="route">Route</label>';
            echo '<select id="route" name="route">';
            foreach ($routes as $r) {
                $selected = ($r['id'] == $selRoute) ? ' selected' : '';
                echo '<option value="' . (int)$r['id'] . '"' . $selected . '>'
                . esc($r['source'] . ' - ' . $r['dest']) . '</option>';
            }
            echo '</select></div>';

            echo '<div class="field"><label for="date">Travel date</label>';
            echo '<input id="date" type="date" name="date" value="' . esc(isset($_SESSION['date']) ? $_SESSION['date'] : '') . '"></div>';

            echo '<div class="field"><label for="seatCount">Number of tickets</label>';
            echo '<input id="seatCount" type="number" name="seatCount" min="1" max="20" value="' . esc(isset($_SESSION['seatCount']) ? $_SESSION['seatCount'] : '') . '"></div>';
            echo '</div>'; // field-grid

            echo '<p id="trip-message" class="trip-message" style="display:none;color:var(--muted-grey);font-size:0.9rem;margin-top:8px;"></p>';
            echo '<input class="find-flights" type="submit" value="Find flights">';
            echo '</form></section>';

            // Tab switching JS
            echo '<script>
            document.querySelectorAll(".trip-tab").forEach(function(btn) {
                btn.addEventListener("click", function() {
                    document.querySelectorAll(".trip-tab").forEach(function(b) { b.classList.remove("is-active"); });
                    this.classList.add("is-active");
                    var type = this.getAttribute("data-trip");
                    document.getElementById("trip_type_form").value = type;
                    var msg = document.getElementById("trip-message");
                    if (type === "oneway") {
                        msg.style.display = "none";
                    } else {
                        msg.style.display = "block";
                        msg.textContent = (type === "roundtrip")
                            ? "Round Trip is coming soon. Currently only One Way is available."
                            : "Multi-City is coming soon. Currently only One Way is available.";
                    }
                });
            });
            </script>';
        }

        protected function resultsPane($flights = null, $selectedId = null) {
            echo '<section class="flight-results" aria-label="Available flight results">';

            if (is_array($flights)) {
                foreach ($flights as $f) {
                    // $cls = ($selectedId === $f['flight_id']) ? ' is-selected' : '';
                    $cls = ((int)$selectedId === (int)$f['flight_id']) ? ' is-selected' : '';
                    echo '<article class="flight-card' . $cls . '">';
                    echo '<div class="flight-top">';
                    if (is_file($f['logo'])) {
                        echo '<img class="airline-logo" src="' . esc($f['logo']) . '" alt="">';
                    }
                    echo '<div class="airline"><strong>' . esc($f['airline']) . '</strong><br>';
                    echo '<span>Flight ' . esc($f['flight_code']) . ' · ' . esc($f['date']) . '</span></div>';
                    if (!empty($f['duration'])) {
                        echo '<span class="chip">' . esc($f['duration']) . '</span>';
                    }
                    echo '<div class="price"><strong>' . esc($f['fare']) . '</strong><br><span>per person</span></div>';
                    echo '</div>';

                    echo '<div class="route">';
                    echo '<div><span class="time">' . esc($f['departs']) . '</span><span class="city">' . esc($f['from']) . '</span></div>';
                    echo '<div class="line"></div>';
                    echo '<div class="end"><span class="time">' . esc($f['arrives']) . '</span><span class="city">' . esc($f['to']) . '</span></div>';
                    echo '</div>';

                    echo '<div class="flight-actions">';
                    echo '<a class="button" href="controller.php?cmd=selectFlight&amp;flightid=' . (int)$f['flight_id'] . '">Select flight</a>';
                    echo '</div>';
                    echo '</article>';
                }
            }
            echo '</section>';
        }

        protected function resultsSummaryPane($flights = null) {
            $n = is_array($flights) ? count($flights) : 0;

            $msg = ($flights === null)
                ? 'Search for a route and date to see flights.'
                : ($n
                    ? $n . ' flight' . ($n === 1 ? '' : 's') . ' found for your search'
                    : 'No flights found for those dates.');

            echo '<section class="search-card results-summary" aria-labelledby="results-title">';
            echo '<div class="results-header">';
            echo '<h2 id="results-title">Available flights</h2>';
            echo '<p>' . esc($msg) . '</p>';
            echo '</div>';
            echo '</section>';
        }

        protected function detailsPane($selected = null) {
            echo '<section class="search-card flight-details-card" aria-labelledby="details-title">';
            echo '<h2 id="details-title">Flight details</h2>';

            if (!$selected) {
                echo '<p>Select a flight to see its details and add it to your cart.</p>';
            } else {
                $flightId = (int)$selected['flight_id'];

                $cart = $_SESSION['cart'] ?? null;
                $isInCart = $cart instanceof Cart
                    && $cart->getFlight($flightId) !== null;

                if ($isInCart) {
                    echo '<span class="cart-status-pill">Booked</span>';
                }

                echo '<p><strong>' . esc($selected['airline']) . '</strong> · Flight ' . esc($selected['flight_code']) . '</p>';
                echo '<p>' . esc($selected['from']) . ' → ' . esc($selected['to']) . '<br>';
                echo esc($selected['date']) . ', ' . esc($selected['departs']) . ' – ' . esc($selected['arrives']);

                if (!empty($selected['duration'])) {
                    echo ' (' . esc($selected['duration']) . ')';
                }

                echo '</p>';
                echo '<p>Fare ' . esc($selected['fare']) . ' × ' . (int)($_SESSION['seatCount'] ?? 1);
                echo ' = <strong>' . esc($selected['total']) . '</strong></p>';

                if (!$isInCart) {
                    echo '<form action="controller.php" method="post">';
                    echo '<input type="hidden" name="cmd" value="addFlightToCart">';
                    echo '<input type="hidden" name="flightDate" value="' . esc($selected['date']) . '">';
                    echo '<input type="hidden" name="flightId" value="' . $flightId . '">';
                    echo '<input type="submit" value="Add to Cart">';
                    echo '</form>';
                }
            }

            echo '</section>';
        }
    }

    // Public destination after logout. This deliberately uses the outer shell
    // rather than the authenticated customer inner view.
    class PublicHomeView extends PublicBaseView {
        use FlightWorkspace;


        public function __construct($flights = null, $selectedId = null) {
            $this->top(false);
            $this->menu(false);
            $this->renderIdentity();
            $this->content($flights, $selectedId);
            $this->bottom();
        }

        public function content($flights = null, $selectedId = null) {
            $this->renderWorkspace(false, $flights, $selectedId);
        }

    }

    // Shared implementation foundation for the separate customer and
    // administrator inner views.
    abstract class PrivateBaseView {

        protected function top($info = false, $stylesheet = 'insidestyles.css') {
            echo '
                    <!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>PHP Airlines</title>
                        <link rel="stylesheet" href="' . htmlspecialchars($stylesheet, ENT_QUOTES, 'UTF-8') . '">
                    </head>
                    <body class="public-page">
                        <div id="main" class="old">
                            <header id="header" class="old">
                                <h1>PHP Airlines.com</h1>
                                <p class="tagline">Your journey starts here</p>
                            </header>
                    ';
            echo FlashMessage::render();
            echo '
                <script>
                document.querySelectorAll(".flash-message__close").forEach(function(button) {
                    button.addEventListener("click", function() {
                        var message = this.closest(".flash-message");

                        if (message) {
                            message.remove();
                        }
                    });
                });
                </script>
                ';
        }


        protected function renderIdentity() {
            $username = $_SESSION['username'] ?? 'Guest';

            if ($username === 'Guest') {
                return;
            }

            $class = ($username === 'rmiller')
                ? 'identity--admin'
                : 'identity--customer';

            echo '<div id="identity" class="identity ' . $class . '">'
                . htmlspecialchars($username, ENT_QUOTES, 'UTF-8')
                . '</div>';
        }

        protected function bottom() {
            echo '
                        <footer id="footer" class="old">
                            <span>PHP Airlines.com</span>
                            <a href="controller.php">Public home</a>
                        </footer>
                    </div>
                </body>
                </html>
                ';
        }
    }

    abstract class CustomerBaseView extends PrivateBaseView {

        public function menu($info = false) {
            $username = $_SESSION['username'] ?? 'Guest';
            $loggedIn = ($username !== 'Guest');

            $this->renderCustomerMenu($loggedIn);
            $this->renderIdentity();
        }

        protected function renderCustomerMenu($loggedIn) {

            echo '<nav id="menu" class="old" aria-label="Main navigation">
                <div class="menu-left">';

            renderAirlineNavLink(
                'controller.php',
                'Home',
                array('', 'showHome', 'searchFlights', 'selectFlight', 'addFlightToCart')
            );

            echo '</div><div class="menu-right">';

            renderAirlineNavLink(
                'controller.php?cmd=showCheckout',
                'Cart',
                array('showCheckout', 'removeCartItem', 'showSeatSelection', 'updateCartSeats'),
                airlineCartHasFlights()
            );

            renderAirlineNavLink(
                'controller.php?cmd=showPassengerDetails',
                'Checkout',
                array(
                    'showPassengerDetails',
                    'submitPassengerDetails',
                    'startStripeCheckout',
                    'stripeSuccess',
                    'stripeCancel'
                ),
                airlineSeatsSelected()
            );

            if ($loggedIn) {
                renderAirlineNavLink(
                    'controller.php?cmd=logout',
                    'Logout',
                    array()
                );
            } else {
                renderAirlineNavLink(
                    'controller.php?cmd=showLogin',
                    'Login / Register',
                    array('showLogin', 'showRegistration', 'authenticateUser')
                );
            }

            echo '</div></nav>';
        }

    }

    abstract class AdminBaseView extends PrivateBaseView {

        public function menu($info = false) {

            echo '<nav id="menu" class="old" aria-label="Administrator navigation">';

            renderAirlineNavLink(
                'controller.php',
                'Public home',
                array('')
            );

            renderAirlineNavLink(
                'controller.php?cmd=showAdminDashboard',
                'Routes',
                array('showAdminDashboard')
            );

            renderAirlineNavLink(
                'controller.php?cmd=showAddRoute',
                'Add route',
                array('showAddRoute')
            );

            renderAirlineNavLink(
                'controller.php?cmd=showFlightManifest',
                'View manifest',
                array('showFlightManifest')
            );

            renderAirlineNavLink(
                'controller.php?cmd=logout',
                'Logout',
                array()
            );

            echo '</nav>';

            $this->renderIdentity();
        }
    }

    
    /**
     * Shared renderer for aircraft seat maps.
     *
     * The database supplies the seats, rows, premium status and occupancy.
     * The aircraft code determines where the aisle gaps appear.
     */
    class SeatMapRenderer {

        public static function render($flightId, $seatMap, $passengerCount, $selectedSeats = array()) {

            if (empty($seatMap)) {
                echo '<p class="seat-map-error">Seat information is unavailable for this flight.</p>';
                return;
            }

            $aircraftCode = $seatMap[0]['aircraft_code'];
            $flightCode = $seatMap[0]['flight_code'];

            if ($aircraftCode === 'AC180') {
                $groups = array(
                    array('A', 'B', 'C'),
                    array('D', 'E', 'F')
                );
            } elseif ($aircraftCode === 'AC220') {
                $groups = array(
                    array('A', 'B', 'C'),
                    array('D', 'E', 'F', 'G'),
                    array('H', 'I', 'J')
                );
            } else {
                echo '<p class="seat-map-error">Unknown aircraft layout: '
                    . esc($aircraftCode) . '</p>';
                return;
            }

            $seatsByRow = array();

            foreach ($seatMap as $seat) {
                $row = (int)$seat['seat_row'];
                $seatsByRow[$row][] = $seat;
            }

            $selectedSeats = array_map('intval', $selectedSeats);

            echo '<section class="seat-map-panel">';
            echo '<h2>' . esc($flightCode) . ' — '
                . esc($aircraftCode) . '</h2>';
            echo '<p>Select one seat for each of the '
                . (int)$passengerCount . ' passengers.</p>';

            echo '<div class="seat-map-legend">';
            echo '<span class="seat-legend available">Available</span>';
            echo '<span class="seat-legend premium">Premium</span>';
            echo '<span class="seat-legend booked">Booked</span>';
            echo '<span class="seat-legend selected">Selected</span>';
            echo '</div>';

            echo '<div class="aircraft-seat-map seat-map-scroll" data-flight-id="'
                . (int)$flightId . '" data-passenger-count="'
                . (int)$passengerCount . '">';

            echo '<div class="aircraft-front">FRONT OF AIRCRAFT</div>';

            foreach ($seatsByRow as $rowNumber => $rowSeats) {

                $seatsByPosition = array();

                foreach ($rowSeats as $seat) {
                    $seatsByPosition[$seat['seat_position']] = $seat;
                }

                echo '<div class="seat-row">';
                echo '<span class="seat-row-number">'
                    . (int)$rowNumber . '</span>';

                foreach ($groups as $groupIndex => $group) {

                    if ($groupIndex > 0) {
                        echo '<span class="seat-aisle" aria-hidden="true"></span>';
                    }

                    echo '<div class="seat-group">';

                    foreach ($group as $position) {

                        if (!isset($seatsByPosition[$position])) {
                            echo '<span class="seat-placeholder"></span>';
                            continue;
                        }

                        $seat = $seatsByPosition[$position];
                        $seatNumber = (int)$seat['seat_no'];
                        $seatLabel = $seat['seat_label'];
                        $isBooked = ((int)$seat['is_booked'] === 1);
                        $isPremium = ((int)$seat['is_premium'] === 1);
                        $isSelected = in_array($seatNumber, $selectedSeats, true);

                        $classes = array('seat-option');

                        if ($isPremium) {
                            $classes[] = 'is-premium';
                        }

                        if ($isBooked) {
                            $classes[] = 'is-booked';
                        }

                        if ($isSelected) {
                            $classes[] = 'is-selected';
                        }

                        echo '<label class="' . esc(implode(' ', $classes)) . '">';

                        echo '<input type="checkbox"'
                            . ' name="seatSelections[' . (int)$flightId . '][]"'
                            . ' value="' . $seatNumber . '"'
                            . ($isBooked ? ' disabled' : '')
                            . ($isSelected ? ' checked' : '')
                            . '>';

                        echo '<span>' . esc($seatLabel) . '</span>';
                        echo '</label>';
                    }

                    echo '</div>';
                }

                echo '</div>';
            }

            echo '<div class="aircraft-rear">REAR OF AIRCRAFT</div>';
            echo '</div>';
            echo '</section>';
        }
    }


    /**
     * Shared workflow for guest and authenticated seat-selection views.
     */
    trait SeatSelectionWorkspace {

        protected function renderSeatSelectionContent() {

            $cart = $_SESSION['cart'] ?? null;

            echo '<div id="content" class="seat-selection-page">';

            if (!$cart || empty($cart->getCartElements())) {
                echo '<div class="seat-selection-heading-row">';
                echo '<div class="workflow-heading">';
                echo '<h1>Your Shopping Cart</h1>';
                echo '<span class="seat-selection-title">Select Your Seats</span>';
                echo '</div>';
                echo '<span class="step-indicator">Step 1/2</span>';
                echo '</div>';
                echo '<p>Your Cart is empty.</p>';
                echo '<a href="controller.php">Return to flight search</a>';
                echo '</div>';
                return;
            }

            $db = new DBManager();

            echo '<form id="seat-selection-form" action="controller.php" method="post">';
            echo '<input type="hidden" name="cmd" value="updateCartSeats">';
            echo '<div class="seat-selection-heading-row">';
            echo '<div class="workflow-heading">';
            echo '<h1>Your Shopping Cart</h1>';
            echo '<span class="seat-selection-title">Select Your Seats</span>';
            echo '</div>';
            echo '<span class="step-indicator">Step 2/2</span>';
            echo '<button type="submit" class="seat-save-button" disabled>'
                . 'Enter Passenger Details</button>';
            echo '</div>';

            foreach ($cart->getCartElements() as $flightId => $flight) {

                $seatMap = $db->getSeatMapForFlight($flightId);
                $selectedSeats = $flight['seatSelections'] ?? array();

                SeatMapRenderer::render(
                    $flightId,
                    $seatMap,
                    (int)$flight['seatCount'],
                    $selectedSeats
                );
            }

            echo '</form>';
            echo '</div>';

            // Limit selections independently for each flight.
            echo '<script>
            var seatSaveButton = document.querySelector(".seat-save-button");
            var seatMaps = document.querySelectorAll(".aircraft-seat-map");

            function updateSeatSaveButton() {
                var allComplete = true;

                seatMaps.forEach(function(map) {
                    var limit = parseInt(map.dataset.passengerCount, 10);
                    var selected = map.querySelectorAll(
                        "input[type=checkbox]:checked"
                    );

                    if (selected.length !== limit) {
                        allComplete = false;
                    }
                });

                if (seatSaveButton) {
                    seatSaveButton.disabled = !allComplete;
                }
            }

            seatMaps.forEach(function(map) {

                var limit = parseInt(map.dataset.passengerCount, 10);
                var checkboxes = map.querySelectorAll(
                    "input[type=checkbox]:not(:disabled)"
                );

                checkboxes.forEach(function(checkbox) {
                    checkbox.addEventListener("change", function() {

                        var selected = map.querySelectorAll(
                            "input[type=checkbox]:checked"
                        );

                        if (selected.length > limit) {
                            this.checked = false;
                            alert("Please select no more than " + limit + " seats for this flight.");
                        }

                        var label = this.closest(".seat-option");

                        if (label) {
                            label.classList.toggle("is-selected", this.checked);
                        }

                        updateSeatSaveButton();
                    });
                });
            });

            updateSeatSaveButton();
            </script>';
        }
    }


    /**
     * Guest seat selection.
     */
    class GuestSeatSelectionView extends PublicBaseView {

        use SeatSelectionWorkspace;

        public function __construct() {
            $this->top(false, 'insidestyles.css');
            $this->menu();
            $this->renderIdentity();
            $this->renderSeatSelectionContent();
            $this->bottom();
        }
    }


    /**
     * Authenticated customer seat selection.
     */
    class CustomerSeatSelectionView extends CustomerBaseView {

        use SeatSelectionWorkspace;

        public function __construct($hasCookie = false) {
            $this->top($hasCookie);
            $this->menu($hasCookie);
            $this->renderSeatSelectionContent();
            $this->bottom();
        }
    }

    /**
     * Cart
     *
     * Manages a customer's flight selections throughout the booking process.
     *
     * Stores selected flights, passenger information, seat selections and fares
     * in the session-based cart. Supports adding and removing flights, updating
     * passenger details, recording seat selections and calculating the total fare.
     *
     * The cart is available to both Guest and authenticated customers. Guest
     * booking information is retained until checkout, where authentication is
     * required before a booking can be completed.
     *
     * Booking persistence is handled separately and must only take place after
     * the customer has authenticated and confirmed the booking.
     */
    class Cart {

        private $cartElements = array();
        private $bookingPassengers = array();


        public function getBookingPassengers() {
            return $this->bookingPassengers;
        }

        /**
         * Get the cart elements.
         */
        public function getCartElements() {
            return $this->cartElements;
        }

        public function exportBookingState() {
            return array(
                'cartElements' => $this->cartElements,
                'bookingPassengers' => $this->bookingPassengers
            );
        }

        public static function fromBookingState($state) {
            if (!is_array($state)
                || !isset($state['cartElements'], $state['bookingPassengers'])
                || !is_array($state['cartElements'])
                || !is_array($state['bookingPassengers'])) {
                return null;
            }

            $cart = new self();
            $cart->cartElements = $state['cartElements'];
            $cart->bookingPassengers = $state['bookingPassengers'];

            return $cart;
        }


        public function addFlight($ticketInfo) {

            $flightId = (int)($ticketInfo['flight_id'] ?? 0);
            $passengerCount = (int)($ticketInfo['seatCount'] ?? 0);

            if ($flightId <= 0 || $passengerCount <= 0 || $passengerCount > 20) {
                return false;
            }

            if (isset($this->cartElements[$flightId])) {
                return true;
            }

            $unitFare = (float)($ticketInfo['unitFare'] ?? 0);

            if ($unitFare <= 0) {
                return false;
            }

            // All flight legs must have the same passenger count.
            if (!empty($this->bookingPassengers) &&
                count($this->bookingPassengers) !== $passengerCount) {
                return false;
            }

            // Create passenger records only once per booking.
            if (empty($this->bookingPassengers)) {

                for ($i = 0; $i < $passengerCount; $i++) {

                    $this->bookingPassengers[] = array(
                        'passengerNumber' => $i + 1,
                        'name' => '',
                        'email' => '',
                        'passport' => ''
                    );
                }
            }

            $ticketInfo['seatCount'] = $passengerCount;
            $ticketInfo['unitFare'] = $unitFare;
            $ticketInfo['totalFare'] = $unitFare * $passengerCount;

            // Seat selections remain specific to each flight.
            $ticketInfo['seatSelections'] = array();

            $this->cartElements[$flightId] = $ticketInfo;

            return true;
        }


        /**
         * Remove a flight from the Cart.
         */
        public function removeFlight($flightId) {

            $flightId = (int)$flightId;

            if (!isset($this->cartElements[$flightId])) {
                return false;
            }

            unset($this->cartElements[$flightId]);

            if (empty($this->cartElements)) {
                $this->bookingPassengers = array();
            }

            return true;
        }
        

        /**
         * Return one flight from the Cart.
         */
        public function getFlight($flightId) {

            $flightId = (int)$flightId;

            return $this->cartElements[$flightId] ?? null;
        }
       
        /**
         * Update passenger details for the booking.
         */
        public function updatePassengers($passengers) {

            if (empty($this->cartElements) || !is_array($passengers)) {
                return false;
            }

            if (count($passengers) !== count($this->bookingPassengers)) {
                return false;
            }

            $passengers = array_values($passengers);

            foreach ($this->bookingPassengers as $index => &$existingPassenger) {

                if (!isset($passengers[$index]) || !is_array($passengers[$index])) {
                    unset($existingPassenger);
                    return false;
                }

                $submittedPassenger = $passengers[$index];

                foreach (['name', 'email', 'passport'] as $field) {

                    if (isset($submittedPassenger[$field]) &&
                        !is_scalar($submittedPassenger[$field])) {
                        unset($existingPassenger);
                        return false;
                    }
                }

                $existingPassenger['name'] =
                    trim((string)($submittedPassenger['name'] ?? ''));

                $existingPassenger['email'] =
                    trim((string)($submittedPassenger['email'] ?? ''));

                $existingPassenger['passport'] =
                    trim((string)($submittedPassenger['passport'] ?? ''));

                if ($existingPassenger['name'] === ''
                    || !filter_var($existingPassenger['email'], FILTER_VALIDATE_EMAIL)
                    || strlen($existingPassenger['name']) > 20
                    || strlen($existingPassenger['email']) > 40
                    || strlen($existingPassenger['passport']) > 10) {
                    unset($existingPassenger);
                    return false;
                }
            }

            unset($existingPassenger);

            return true;
        }

        public function hasCompletePassengerDetails() {
            if (empty($this->bookingPassengers)) {
                return false;
            }

            foreach ($this->bookingPassengers as $passenger) {
                if (empty($passenger['name'])
                    || !filter_var($passenger['email'], FILTER_VALIDATE_EMAIL)
                    || strlen($passenger['name']) > 20
                    || strlen($passenger['email']) > 40
                    || strlen($passenger['passport']) > 10) {
                    return false;
                }
            }

            return true;
        }

        /**
         * Store selected seat numbers for a flight.
         *
         * Each passenger must have one unique, valid seat number.
         * Seat availability is checked separately by processSeatSelections().
         */
        public function updateSeatSelections($flightId, $seatSelections) {

            $flightId = (int)$flightId;

            if (!isset($this->cartElements[$flightId])) {
                return false;
            }

            if (!is_array($seatSelections)) {
                return false;
            }

            $passengerCount = (int)$this->cartElements[$flightId]['seatCount'];

            if (count($seatSelections) !== $passengerCount) {
                return false;
            }

            $validatedSeats = array();

            foreach ($seatSelections as $seat) {

                if (filter_var($seat, FILTER_VALIDATE_INT) === false) {
                    return false;
                }

                $seatNumber = (int)$seat;

                if ($seatNumber <= 0) {
                    return false;
                }

                if (in_array($seatNumber, $validatedSeats, true)) {
                    return false;
                }

                $validatedSeats[] = $seatNumber;
            }

            $this->cartElements[$flightId]['seatSelections'] = $validatedSeats;

            return true;
        }

        /**
         * Calculate the total value of the Cart.
         */
        public function getGrandTotal() {

            $grandTotal = 0;

            foreach ($this->cartElements as $flight) {
                $grandTotal += (float)$flight['unitFare']
                            * (int)$flight['seatCount'];
            }

            return $grandTotal;
        }

        /**
         * Display Cart contents.
         */
        
        public function displayCart() {

            if (empty($this->cartElements)) {

                echo '<p>Your Cart is currently empty.</p>';
                echo '<a href="controller.php">Back to search</a>';

                return;
            }

            echo '<div class="cart-flight-list">';

            foreach ($this->cartElements as $flightId => $flight) {

                $passengers = (int)$flight['seatCount'];
                $unitFare = (float)$flight['unitFare'];
                $flightTotal = $unitFare * $passengers;

                echo '<article class="cart-item cart-flight-card">';
                echo '<div class="cart-flight-details">';
                echo '<h3>' . esc($flight['flight_code'] ?? 'Flight')
                    . ' — ' . esc($flight['aircraft_code'] ?? 'Aircraft') . '</h3>';
                echo '<p>Route: '
                    . esc($flight['from'] ?? '') . ' to '
                    . esc($flight['to'] ?? '') . '</p>';
                echo '<p>Date: ' . esc($flight['date'] ?? '') . '</p>';
                echo '<p>Departure: ' . esc($flight['departs'] ?? '') . '</p>';
                echo '<p>Arrival: ' . esc($flight['arrives'] ?? '') . '</p>';
                echo '<h4>Seat selection</h4>';

                $selectedSeats = $flight['seatSelections'] ?? array();

                if (count($selectedSeats) === $passengers) {
                    echo '<p>Selected seats: '
                        . esc(implode(', ', $selectedSeats))
                        . '</p>';
                } else {
                    echo '<p>Seat selection pending.</p>';
                }
                echo '</div>';

                echo '<div class="cart-flight-fare">';
                echo '<p>Number of passengers: ' . $passengers . '</p>';
                echo '<p>Fare per passenger: €'
                    . number_format($unitFare, 2) . '</p>';
                echo '<p>Flight total: €'
                    . number_format($flightTotal, 2) . '</p>';
                // echo '<a class="button cart-checkout-button"
                //     href="controller.php?cmd=showSeatSelection">
                //     Select Seats
                // </a>';
                echo '<a class="cart-remove-button" href="controller.php?cmd=removeCartItem&amp;flightId='
                    . (int)$flightId . '">Remove Flight</a>';
                echo '</div>';
                echo '</article>';
            }

            echo '</div>';
            echo '<div class="cart-bottom-section">';
            echo '<div class="cart-passengers">';
            echo '<h3>Passengers</h3>';

            foreach ($this->bookingPassengers as $passenger) {

                echo '<p>Passenger '
                    . (int)$passenger['passengerNumber']
                    . ' — ';

                echo !empty($passenger['name'])
                    ? esc($passenger['name'])
                    : 'Details pending';

                echo '</p>';
            }

            echo '</div>';

            echo '<div class="cart-total">';
            echo '<h3>Cart total: €'
                . number_format($this->getGrandTotal(), 2)
                . '</h3>';
            echo '</div>';


            echo '</div>';
        }



        /**
         * Validate all selected seats against current database availability.
         *
         * Every passenger must have one unique seat on each flight.
         * A seat must exist on that aircraft and must not already be booked.
         */
        public function processSeatSelections() {

            if (empty($this->cartElements)) {
                return false;
            }

            $db = new DBManager();

            foreach ($this->cartElements as $flightId => $flight) {

                $passengerCount = (int)$flight['seatCount'];
                $selections = $flight['seatSelections'] ?? array();

                // Every passenger must have a selected seat.
                if (!is_array($selections) ||
                    count($selections) !== $passengerCount) {
                    return false;
                }

                // Normalise seat numbers and reject duplicates.
                $selections = array_map('intval', $selections);

                if (count(array_unique($selections)) !== count($selections)) {
                    return false;
                }

                // Retrieve the actual aircraft seat availability.
                $seatMap = $db->getSeatMapForFlight($flightId);

                if (empty($seatMap)) {
                    return false;
                }

                $availableSeats = array();

                foreach ($seatMap as $seat) {

                    $seatNumber = (int)$seat['seat_no'];

                    if ((int)$seat['is_booked'] === 0) {
                        $availableSeats[] = $seatNumber;
                    }
                }

                // Check that every selected seat exists and is available.
                foreach ($selections as $seatNumber) {

                    if (!in_array($seatNumber, $availableSeats, true)) {
                        return false;
                    }
                }
            }

            return true;
        }

        /**
         * Persist a paid Cart as one transactional booking.
         */
        public function recordBookings($username, $paymentSessionId) {
            if (empty($this->cartElements)
                || !$this->hasCompletePassengerDetails()
                || !$this->processSeatSelections()) {
                return array('success' => false, 'error' => 'The booking details or seat selections are incomplete.');
            }

            $passengers = $this->bookingPassengers;
            $userId = null;
            $reference = 'PA' . strtoupper(bin2hex(random_bytes(6)));
            $db = new DBManager();

            try {
                $db->getConnection()->beginTransaction();

                if ($username && $username !== 'Guest') {
                    $userStmt = $db->getConnection()->prepare('SELECT id FROM user WHERE username = :username');
                    $userStmt->execute(array(':username' => $username));
                    $userId = $userStmt->fetchColumn();
                    if ($userId === false) {
                        throw new RuntimeException('The signed-in customer could not be found.');
                    }
                }

                $bookingStmt = $db->getConnection()->prepare(
                    'INSERT INTO booking
                        (booking_reference, user_id, booking_status, payment_session_id)
                     VALUES (:reference, :user_id, :status, :payment_session_id)'
                );
                $bookingStmt->execute(array(
                    ':reference' => $reference,
                    ':user_id' => $userId,
                    ':status' => 'paid',
                    ':payment_session_id' => $paymentSessionId
                ));
                $bookingId = $db->getConnection()->lastInsertId();

                $sequence = 1;
                foreach ($this->cartElements as $flightId => $flight) {
                    $flightStmt = $db->getConnection()->prepare(
                        'SELECT route_id, flight_date FROM flights
                         WHERE flight_id = :flight_id FOR UPDATE'
                    );
                    $flightStmt->execute(array(':flight_id' => (int)$flightId));
                    $flightRow = $flightStmt->fetch(PDO::FETCH_ASSOC);
                    if (!$flightRow) {
                        throw new RuntimeException('A selected flight is no longer available.');
                    }

                    $linkStmt = $db->getConnection()->prepare(
                        'INSERT INTO booking_flights
                            (booking_id, flight_id, sequence_no)
                         VALUES (:booking_id, :flight_id, :sequence_no)'
                    );
                    $linkStmt->execute(array(
                        ':booking_id' => $bookingId,
                        ':flight_id' => (int)$flightId,
                        ':sequence_no' => $sequence
                    ));
                    $bookingFlightId = $db->getConnection()->lastInsertId();

                    $seatStmt = $db->getConnection()->prepare(
                        'SELECT s.seat_no
                         FROM aircraft_seats s
                         JOIN flights f ON f.aircraft_id = s.aircraft_id
                         LEFT JOIN passangers p
                           ON p.flight_id = f.flight_id AND p.seatNo = s.seat_no
                         WHERE f.flight_id = :flight_id
                           AND s.seat_no = :seat_no
                           AND p.id IS NULL
                         FOR UPDATE'
                    );
                    $insertPassenger = $db->getConnection()->prepare(
                        'INSERT INTO passangers
                            (name, passport, route, date, registered, registeredName,
                             creditCardno, email, seatNo, flight_id, booking_flight_id)
                         VALUES
                            (:name, :passport, :route, :date, :registered, :registered_name,
                             :credit_card, :email, :seat_no, :flight_id, :booking_flight_id)'
                    );

                    foreach ($flight['seatSelections'] as $passengerIndex => $seatNo) {
                        $seatStmt->execute(array(
                            ':flight_id' => (int)$flightId,
                            ':seat_no' => (int)$seatNo
                        ));
                        if (!$seatStmt->fetchColumn()) {
                            throw new RuntimeException('One or more selected seats are no longer available.');
                        }

                        $passenger = $passengers[$passengerIndex];
                        $insertPassenger->execute(array(
                            ':name' => $passenger['name'],
                            ':passport' => $passenger['passport'],
                            ':route' => (int)$flightRow['route_id'],
                            ':date' => $flightRow['flight_date'],
                            ':registered' => $userId ? 1 : 0,
                            ':registered_name' => $username ?: '',
                            ':credit_card' => '',
                            ':email' => $passenger['email'],
                            ':seat_no' => (int)$seatNo,
                            ':flight_id' => (int)$flightId,
                            ':booking_flight_id' => $bookingFlightId
                        ));
                    }
                    $sequence++;
                }

                $db->getConnection()->commit();
                return array('success' => true, 'reference' => $reference);
            } catch (Throwable $e) {
                if ($db->getConnection()->inTransaction()) {
                    $db->getConnection()->rollBack();
                }
                return array('success' => false, 'error' => $e->getMessage());
            }
        }
    }

    // Renders the customer and administrator login form.
    class LoginView extends PublicBaseView {

        public function __construct($info) {
            $this->top($info);
            $this->menu($info);
            $this->content();
            $this->bottom();
        }

        public function menu($info = false) {
            parent::menu($info);
        }

        public function content() {
            echo "
                <div id=\"content\">
                    <h1> login </h1>
                    <FORM METHOD=\"post\" ACTION=\"controller.php\">                
                        <input type=\"hidden\" name=\"cmd\" value=\"authenticateUser\">              
                        Username: <input type=\"text\" name=\"username\"> <br>
                        Password: <input type=\"password\" name=\"password\"> <br>
                        <input type=\"submit\" value=\"authenticateUser\">
                    </FORM>              
                </div>
                ";
            echo '<p class="register-prompt">';
            echo 'Don\'t have an account? ';
            echo '<a href="controller.php?cmd=showRegistration">Register here</a>';
            echo '</p>';
        }
    }

    // Renders the customer registration form.
    class RegistrationView extends PublicBaseView {

        public function __construct() {
            $info = 0;
            $this->top($info);
            $this->menu($info);
            $this->content();
            $this->bottom();
        }

        public function menu($info = false) {
            parent::menu($info);
        }

        public function content() {
            echo "
        <div id=\"content\">
                    <h1> Register </h1>
                        <form action=\"controller.php\" method=\"post\">
                    <input type=\"hidden\" name=\"cmd\" value=\"registerUser\">
                    <table>
                        <tr>
                            <td>username:</td>
                            <td><input type=\"text\" name=\"username\"> </td>

                        </tr>
                        <tr>
                            <td>password:</td>
                            <td><input type=\"password\" name=\"password\"></td>
                        </tr>
                        <tr>
                            <td>e-mail address:</td>
                            <td><input type=\"email\" name=\"email\"></td>
                        </tr>
                    
                        <tr>
                            <td>Credit Card No:</td>
                            <td><input type=\"text\" name=\"CreditCardNo\"></td>
                        </tr>
                        <tr>
                            <td>Passport No:</td>
                            <td><input type=\"text\" name=\"PassportNo\"></td>
                        </tr>
                        <tr>
                            <td><input type=\"submit\" value=\"Submit\"></td>
                            <td></td>

                        </tr> 
                        
                    
                </div>
                </form>
                ";
        }
    }
 
    trait CartWorkspace {

        protected function renderCartContent($isGuest) {
            $cart = $_SESSION['cart'] ?? null;
            $hasFlights = $cart && !empty($cart->getCartElements());

            echo '<div id="content" class="cart-page">';

            echo '<div class="cart-heading-row">';
            echo '<div class="workflow-heading">';
            echo '<h1>Your Shopping Cart</h1>';
            echo '<span class="seat-selection-title">Details</span>';
            echo '</div>';

            if ($hasFlights) {
                echo '<a class="button cart-select-seats-button"'
                    . ' href="controller.php?cmd=showSeatSelection">Select Seats</a>';
            }

            echo '<span class="step-indicator">Step 1/2</span>';
            echo '</div>';

            if (!$hasFlights) {
                echo '<p>Your Cart is currently empty.</p>';
                echo '<a href="controller.php">Back to search</a>';
                echo '</div>';
                return;
            }

            $cart->displayCart();

            if (!$isGuest
                && $cart->hasCompletePassengerDetails()
                && $cart->processSeatSelections()) {
                echo '<div class="cart-actions">';
                echo '<form action="controller.php" method="post" class="payment-form">';
                echo '<input type="hidden" name="cmd" value="startStripeCheckout">';
                echo '<button type="submit">Pay securely with Stripe</button>';
                echo '</form>';
                echo '</div>';
            }

            echo '</div>';
        }
    }

    /**
     * Displays the session Cart to a Guest.
     */
    class GuestCartView extends PublicBaseView {
        use CartWorkspace;

        public function __construct() {
            $this->top(false, 'insidestyles.css');
            $this->menu();
            $this->renderIdentity();
            $this->renderCartContent(true);
            $this->bottom();
        }
    }

    trait CheckoutWorkspace {

        protected function renderCheckoutContent($isGuest) {
            $cart = $_SESSION['cart'] ?? null;
            $prefix = $isGuest ? 'guest-' : 'customer-';
            $formId = $prefix . 'passenger-details-form';
            $hasPassengerDetails = $cart && $cart->hasCompletePassengerDetails();

            echo '<div id="content" class="checkout-page">';
            echo '<div class="checkout-heading-row">';
            echo '<div class="workflow-heading">';
            echo '<h1>Checkout</h1>';
            echo '<span class="seat-selection-title">'
                . ($hasPassengerDetails ? 'Payment' : 'Passenger Details')
                . '</span>';
            echo '</div>';
            echo '<span class="step-indicator">'
                . ($hasPassengerDetails ? 'Step 2/2' : 'Step 1/2')
                . '</span>';
            if (!$hasPassengerDetails) {
                echo '<button type="submit" form="' . $formId
                    . '" class="checkout-heading-actions" disabled>'
                    . 'Continue to payment</button>';
            }
            echo '</div>';

            if (!$cart || empty($cart->getCartElements())) {
                echo '<p>Your Cart is empty.</p>';
                echo '<a href="controller.php">Return to flight search</a>';
                echo '</div>';
                return;
            }

            if ($isGuest) {
                $_SESSION['booking_return_command'] = 'showPassengerDetails';
            }

            echo '<div class="checkout-layout">';
            echo '<section class="checkout-details">';
            echo '<p>Enter the details for each traveller.</p>';
            echo '<form id="' . $formId . '" action="controller.php" method="post">';
            echo '<input type="hidden" name="cmd" value="submitPassengerDetails">';

            foreach ($cart->getBookingPassengers() as $index => $passenger) {
                $number = (int)($passenger['passengerNumber'] ?? ($index + 1));

                echo '<fieldset class="passenger-details">';
                echo '<legend>Passenger ' . $number . '</legend>';
                echo '<label for="' . $prefix . 'name-' . $index . '">Full name</label>';
                echo '<input type="text" id="' . $prefix . 'name-' . $index
                    . '" name="passengers[' . $index . '][name]" value="'
                    . esc($passenger['name'] ?? '') . '" maxlength="20" required>';
                echo '<label for="' . $prefix . 'email-' . $index . '">Email address</label>';
                echo '<input type="email" id="' . $prefix . 'email-' . $index
                    . '" name="passengers[' . $index . '][email]" value="'
                    . esc($passenger['email'] ?? '') . '" maxlength="40" required>';
                echo '<label for="' . $prefix . 'passport-' . $index . '">Passport number</label>';
                echo '<input type="text" id="' . $prefix . 'passport-' . $index
                    . '" name="passengers[' . $index . '][passport]" value="'
                    . esc($passenger['passport'] ?? '') . '" maxlength="10">';
                echo '</fieldset>';
            }

            echo '</form>';
            echo '</section>';
            echo '<aside class="checkout-summary">';
            echo '<h2>Booking Summary</h2>';
            $grandTotal = 0;

            foreach ($cart->getCartElements() as $flight) {
                $seatCount = (int)$flight['seatCount'];
                $unitFare = (float)$flight['unitFare'];
                $flightTotal = $seatCount * $unitFare;
                $grandTotal += $flightTotal;

                echo '<div class="checkout-flight">';
                echo '<h3>' . esc($flight['flight_code'] ?? 'Flight') . '</h3>';
                echo '<p><strong>' . esc($flight['from'] ?? '') . ' → '
                    . esc($flight['to'] ?? '') . '</strong></p>';
                echo '<p>Date: ' . esc($flight['date'] ?? '') . '</p>';
                echo '<p>Departure: ' . esc($flight['departs'] ?? '') . '</p>';
                echo '<p>Arrival: ' . esc($flight['arrives'] ?? '') . '</p>';
                echo '<p>Passengers: ' . $seatCount . '</p>';
                $selectedSeats = $flight['seatSelections'] ?? array();
                echo '<p>Seats: ' . (count($selectedSeats) === $seatCount
                    ? esc(implode(', ', $selectedSeats))
                    : 'Selection pending.') . '</p>';
                echo '<p>Fare per passenger: €' . number_format($unitFare, 2) . '</p>';
                echo '<p>Flight total: €' . number_format($flightTotal, 2) . '</p>';
                echo '</div>';
            }

            echo '<div class="checkout-total"><h3>Total: €'
                . number_format($grandTotal, 2) . '</h3></div>';
            echo '<div class="checkout-actions">';
            // echo '<a class="button" href="controller.php?cmd=showCheckout">'
            //     . 'Return to Cart</a> ';
            // echo '<a class="button" href="controller.php?cmd=showSeatSelection">'
            //     . 'Change Seats</a>';

            if ($cart->hasCompletePassengerDetails()
                && $cart->processSeatSelections()) {
                if ($isGuest) {
                    echo '<div class="checkout-authentication">';
                    echo '<p>Log in or register to continue to payment.</p>';
                    echo '<a class="button" href="controller.php?cmd=showLogin">'
                        . 'Log in to continue</a> ';
                    echo '<a class="button" href="controller.php?cmd=showRegistration">'
                        . 'Register to continue</a>';
                    echo '</div>';
                } else {
                    echo '<form action="controller.php" method="post" class="payment-form">';
                    echo '<input type="hidden" name="cmd" value="startStripeCheckout">';
                    echo '<button type="submit">Pay securely with Stripe</button>';
                    echo '</form>';
                }
            }

            echo '</div>';
            echo '</aside>';
            echo '</div>';
            echo '</div>';
            if (!$hasPassengerDetails) {
                echo '<script>
                (function() {
                    var form = document.getElementById("' . $formId . '");
                    var button = document.querySelector(".checkout-heading-actions");
                    if (!form || !button) {
                        return;
                    }

                    function updateContinueButton() {
                        button.disabled = !form.checkValidity();
                    }

                    form.addEventListener("input", updateContinueButton);
                    form.addEventListener("change", updateContinueButton);
                    updateContinueButton();
                })();
                </script>';
            }
        }
    }

    /**
     * Guest checkout: passenger details can be entered before
     * authentication is required for payment.
     */
    class GuestCheckoutView extends PublicBaseView {
        use CheckoutWorkspace;

        public function __construct() {
            $this->top(false, 'insidestyles.css');
            $this->menu();
            $this->renderIdentity();
            $this->renderCheckoutContent(true);
            $this->bottom();
        }
    }

    class BookingConfirmationView extends PublicBaseView {

            private $reference;

            public function __construct($reference) {
                $this->reference = $reference;
                $this->top();
                $this->menu();
                $this->content();
                $this->bottom();
            }

            public function content() {
                echo '<div id="content" class="checkout-page">';
                echo '<h1>Booking confirmed</h1>';
                echo '<p>Thank you. Your booking reference is <strong>'
                    . esc($this->reference)
                    . '</strong>.</p>';
                echo '<p>Your payment was confirmed and your seats have been allocated.</p>';
                echo '<p><a class="button" href="controller.php">Return to home</a></p>';
                echo '</div>';
            }
    }

    // Renders the flight search form (logged-in customer).
    class CustomerHomeView extends CustomerBaseView {
        use FlightWorkspace;

        protected $flights = null;
        protected $selectedId = null;

        public function __construct($info, $flights = null, $selectedId = null) {
            $this->flights    = $flights;
            $this->selectedId = $selectedId;
            $this->top($info);
            $this->menu($info);
            $this->content();
            $this->bottom();
        }

        public function content() {
            $this->renderWorkspace(true, $this->flights, $this->selectedId);
        }

        // Keep top() override that was in the original for welcome message (currently commented)
        function top($hasCookie = false, $stylesheet = 'insidestyles.css') {
            parent::top($hasCookie, $stylesheet);
            $username = isset($_COOKIE['username']) ? $_COOKIE['username'] : (isset($_SESSION['username']) ? $_SESSION['username'] : '');
        }

        function bottom() {
            parent::bottom();
        }
    }

    /**
     * Displays flight-search results for a selected route, ticket quantity
     * and travel date. Uses the new FlightWorkspace layout.
     */
    class CustomerFlightResultsView extends CustomerHomeView {

        public function __construct($route, $seatCount, $date, $info, $flights = null, $selectedId = null) {
            $_SESSION['route']     = $route;
            $_SESSION['seatCount'] = $seatCount;
            $_SESSION['date']      = $date;

            parent::__construct($info, $flights, $selectedId);
        }
    }

    /**
    * Displays passenger details for the current booking.
    */
    class CustomerCheckoutView extends CustomerHomeView {
        use CheckoutWorkspace;

        public function __construct($hasCookie) {
            $this->top($hasCookie);
            $this->menu($hasCookie);
            $this->renderCheckoutContent(false);
            $this->bottom();
        }
    }
   
    /**
     * Displays the checkout page.
     */
    class CustomerCartView extends CustomerHomeView {
        use CartWorkspace;

        public function __construct($info) {
            $this->top($info);
            $this->menu($info);
            $this->renderCartContent(false);
            $this->bottom();
        }
    }

    // Renders the administrator route-management page.
    class AdminRoutesView extends AdminBaseView {

        public function __construct($info = false) {
            $this->top();
            $this->menu();
            $this->content($info);
            $this->bottom();
        }

        public function content($info) {
            echo "<div id=\"content\" class=\"triple-pane\">
                <section class=\"pane\">
                    <h1>Routes</h1>
                    <FORM METHOD=\"post\" ACTION=\"controller.php\">
                        <input type=\"hidden\" name=\"cmd\" value=\"deleteSelectedRoutes\">
                    
                        <input type=\"hidden\" name=\"blogtitle\" value=>";
            $DBM = new DBManager();
            $DBM->displayRouteManagementList();
            echo"     <input type=\"submit\" value=\"delete routes\">                   
                    </FORM>
                </section>
                <section class=\"pane\">
                    <h2>Route details</h2>
                    <p>Select a route to edit its price or create a new route.</p>
                    <a class=\"button\" href=\"controller.php?cmd=showAddRoute\">Add route</a>
                </section>
                <section class=\"pane\">
                    <h2>Bookings and manifest</h2>
                    <p>Review flights associated with a route.</p>
                    <a class=\"button\" href=\"controller.php?cmd=showFlightManifest\">View manifest</a>
                </section>
            </div>";
        }

        function bottom() {
            parent::bottom();
        }
    }

    // Current name for the administrator route-management page.
    class AdminView extends AdminRoutesView {
    }

    // Renders the administrator route-editing page.
    class AdminEditRouteView extends AdminBaseView {

        public function __construct($info) {
            $this->top();
            $this->menu();
            $this->content($info);
            $this->bottom();
        }

        public function content($info) {
            echo "<div id=\"content\">
                    <h1> Administrator  -Edit Route# {$info} </h1>
                    <FORM METHOD=\"post\" ACTION=\"controller.php\">
                        <input type=\"hidden\" name=\"cmd\" value=\"updateRoute\">
                        <input type=\"hidden\" name=\"index\" value=$info>";
            $DBM = new DBManager();
            $DBM->displayRouteForEditing($info);
            echo"     <input type=\"submit\" value=\"editPrice\">                       
                    </FORM>
                ";
            echo "</div>";
        }

        public function menu($info = false) {
            parent::menu($info);
        }

        function bottom() {
            parent::bottom();
        }
    }

    // Renders the administrator add-route page.
    class AdminAddRouteView extends AdminBaseView {

        public function __construct($info) {
            $this->top();
            $this->menu();
            $this->content($info);
            $this->bottom();
        }

        public function content($info) {
            echo "<div id=\"content\">
                    <h1> Administrator  -Add Route# - max. 8 </h1>
                    <FORM METHOD=\"post\" ACTION=\"controller.php\">
                        <input type=\"hidden\" name=\"cmd\" value=\"createRoute\">
                    
                        <input type=\"hidden\" name=\"index\" value=$info>";
            $DBM = new DBManager();
            $DBM->displayRouteDetails($info);
            echo "<table>
                        <tr>
                            <td>Source:</td>
                            <td><input type=\"text\" name=\"source\"> </td>
                        </tr>
                        <tr>
                            <td>Destination:</td>
                            <td><input type=\"text\" name=\"destination\"></td>
                        </tr>
                        <tr>
                            <td>Operational from:</td>
                            <td><input type=\"date\" name=\"operationalFrom\"></td>
                        </tr>
                    <tr> <td>e.g. 01-Jan-2014</td> </tr>
                        <tr>
                            <td>Operational to:</td>
                            <td><input type=\"date\" name=\"operationalTo\"></td>
                        </tr>
                        <tr> <td>  </td> </tr>
                        <tr> <td>e.g. 01-Feb-2014</td> </tr>
                        <tr>
                            <td>Price:</td>
                            <td><input type=\"number\" name=\"Price\"></td>
                        </tr>
                        <tr>
                            <td><input type=\"submit\" value=\"createRoute\"></td>
                            <td></td>

                        </tr>                    
                </div>
                </form>
                ";
            echo"  </FORM>
            ";
            echo "</div>";
        }

        public function menu($info = false) {
            parent::menu($info);
        }

        function bottom() {
            parent::bottom();
        }
    }

    // Renders the administrator flight manifest date list.
    class AdminFlightManifestView extends AdminBaseView {

        public function __construct($info) {
            $this->top();
            $this->menu();
            $this->content($info);
            $this->bottom();
        }

        public function content($info) {
            echo "<div id=\"content\">
                    <h1> Administrator  -Display flights by date # - </h1>
                    <FORM METHOD=\"post\" ACTION=\"controller.php\">
                        <input type=\"hidden\" name=\"cmd\" value=\"viewFlightdetails\">
                    
                        <input type=\"hidden\" name=\"index\" value=$info>";
            $DBM = new DBManager();
            $DBM->displayBookedFlights($info);
            echo "<table>
                        <tr>
                            <td> 
                        <tr>
                            <td><input type=\"submit\" value=\"createRoute\"></td>
                            <td></td>

                        </tr> 
                        
                    
                </div>
                </form>
                ";
            echo"  </FORM>
            ";
            echo "</div>";
        }

        public function menu($info = false) {
            parent::menu($info);
        }

        function bottom() {
            parent::bottom();
        }
    }

    /**
     * Manages the database connection and provides methods for user authentication,
     * route management, customer bookings, seat availability and flight manifests.
     */
    class DBManager {

        private $db;

        public function __construct() {
            $address = 'mysql:host=127.0.0.1;port=3307;dbname=airline2026';
            $username = 'root';
            $password = 'Spring57Local!';

            try {
                $this->db = new PDO($address, $username, $password, array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ));
                $this->db->exec(
                    'CREATE TABLE IF NOT EXISTS customer_booking_state (
                        user_id INT NOT NULL PRIMARY KEY,
                        state_json LONGTEXT NOT NULL,
                        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                            ON UPDATE CURRENT_TIMESTAMP,
                        CONSTRAINT fk_customer_booking_state_user
                            FOREIGN KEY (user_id) REFERENCES user(id)
                            ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
                );
            } catch (PDOException $e) {
                $error_message = $e->getMessage();
                echo $error_message;
                exit();
            }
        }

        public function getConnection() {
            return $this->db;
        }

        public function loadCustomerBookingState($username) {
            $stmt = $this->db->prepare(
                'SELECT s.state_json
                 FROM customer_booking_state s
                 JOIN user u ON u.id = s.user_id
                 WHERE u.username = :username'
            );
            $stmt->execute(array(':username' => $username));
            $json = $stmt->fetchColumn();

            if ($json === false) {
                return null;
            }

            $state = json_decode($json, true);
            return Cart::fromBookingState($state);
        }

        public function saveCustomerBookingState($username, Cart $cart) {
            $userId = $this->getCustomerId($username);
            if (!$userId) {
                return false;
            }

            $state = $cart->exportBookingState();
            if (empty($state['cartElements'])) {
                return $this->deleteCustomerBookingState($username);
            }

            $stmt = $this->db->prepare(
                'INSERT INTO customer_booking_state (user_id, state_json)
                 VALUES (:user_id, :state_json)
                 ON DUPLICATE KEY UPDATE
                    state_json = VALUES(state_json),
                    updated_at = CURRENT_TIMESTAMP'
            );
            return $stmt->execute(array(
                ':user_id' => $userId,
                ':state_json' => json_encode($state, JSON_THROW_ON_ERROR)
            ));
        }

        public function deleteCustomerBookingState($username) {
            $stmt = $this->db->prepare(
                'DELETE s FROM customer_booking_state s
                 JOIN user u ON u.id = s.user_id
                 WHERE u.username = :username'
            );
            return $stmt->execute(array(':username' => $username));
        }

        private function getCustomerId($username) {
            $stmt = $this->db->prepare(
                'SELECT id FROM user WHERE username = :username'
            );
            $stmt->execute(array(':username' => $username));
            $id = $stmt->fetchColumn();
            return $id === false ? null : (int)$id;
        }

        public function getBookingByPaymentSession($paymentSessionId) {
            $stmt = $this->db->prepare(
                'SELECT booking_reference
                 FROM booking
                 WHERE payment_session_id = :payment_session_id'
            );
            $stmt->execute(array(':payment_session_id' => $paymentSessionId));
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        /**
         * New method used by the flight search results.
         */
        public function getFlightsForSearch($routeId, $fromDate, $toDate) {
            $sql = "SELECT f.flight_id, f.flight_code, f.flight_date,
                        f.departure_time, f.arrival_time,
                        r.source, r.dest, r.price
                    FROM flights f
                    JOIN routes r ON r.id = f.route_id
                    WHERE f.route_id = :route
                    AND f.flight_date BETWEEN :d_from AND :d_to
                    ORDER BY f.flight_date, f.departure_time";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array(
                ':route'  => (int)$routeId,
                ':d_from' => $fromDate,
                ':d_to'   => $toDate
            ));
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function getFlightForCart($flightId)
        {
            $sql = "SELECT f.flight_id, f.flight_code, f.flight_date,
                        f.departure_time, f.arrival_time, f.route_id,
                        r.source, r.dest, r.price, a.aircraft_code
                    FROM flights f
                    JOIN routes r ON r.id = f.route_id
                    JOIN aircraft a ON a.aircraft_id = f.aircraft_id
                    WHERE f.flight_id = :flight_id";

            $stmt = $this->db->prepare($sql);

            $stmt->execute(array(
                ':flight_id' => (int)$flightId
            ));

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        /**
         * Retrieve the aircraft seat map and occupancy for a specific flight.
         *
         * Aircraft layout comes from aircraft and aircraft_seats.
         * Seat occupancy is determined separately for each flight
         * using the passangers table.
         *
         * Returns one row per aircraft seat.
         */
        public function getSeatMapForFlight($flightId) {

            $sql = "
                SELECT
                    f.flight_id,
                    f.flight_code,
                    f.flight_date,

                    a.aircraft_id,
                    a.aircraft_code,
                    a.model,
                    a.configuration_name,
                    a.capacity,

                    s.aircraft_seat_id,
                    s.seat_no,
                    s.seat_label,
                    s.seat_row,
                    s.seat_position,
                    s.is_premium,

                    CASE
                        WHEN p.id IS NULL THEN 0
                        ELSE 1
                    END AS is_booked

                FROM flights f

                JOIN aircraft a
                    ON a.aircraft_id = f.aircraft_id

                JOIN aircraft_seats s
                    ON s.aircraft_id = a.aircraft_id

                LEFT JOIN passangers p
                    ON p.flight_id = f.flight_id
                    AND p.seatNo = s.seat_no

                WHERE f.flight_id = :flight_id

                ORDER BY s.seat_row, s.seat_no
            ";

            $stmt = $this->db->prepare($sql);

            $stmt->execute(array(
                ':flight_id' => (int)$flightId
            ));

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function authenticateUser($username, $password) {
            $query = "select * from user";
            $resultSet = $this->db->query($query);
            while ($row = $resultSet->fetch()) {
                if (($row['username'] == $username) && ($row['password'] == $password)) {
                    if ($username == "rmiller") {
                        return "rmiller";
                    } else {
                        return "user";
                    }
                }
            }
            return "invalid";
        }

        public function registerUser(&$hasCookie) {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $email = $_POST['email'];
            $CreditCardNo = $_POST['CreditCardNo'];
            $PassportNo = $_POST['PassportNo'];
            $suspend = 0;

            $user = array();
            $data = array();

            $query = "INSERT INTO user (username,password,email,CreditCardNo,passport,suspend)
                                VALUE ('$username','$password','$email','$CreditCardNo',{$PassportNo},{$suspend})";
            $result = $this->db->exec($query);

            $query1 = "select * from user";
            $resultSet = $this->db->query($query1);

            $id = 0;
            for ($i = 0; $i < $resultSet->rowCount(); $i++) {
                $user = $resultSet->fetch();
                if ($username == $user['username']) {
                    $id = $user['id'];
                    $data['id'] = $id;
                }
            }
            setCookie('username', $_POST['username'], time() + 3600, '/');
            $hasCookie = 1;
            $_SESSION['username'] = $_POST['username'];

            $data['email'] = $email;
            $data['CreditCardNo'] = $CreditCardNo;
            $data['username'] = $username;
            $data['id'] = $id;
            return $data;
        }

        public function updateRoutePrice($value, $index) {
            $query = "update routes set price={$value} where id={$index}";
            $resultSet = $this->db->query($query);
        }

        public function checkSeats() {
            return true;
        }

        public function getAllRoutes() {
            $query = "select * from routes";
            $resultSet = $this->db->query($query);
            return $resultSet->fetchAll();
        }

        function getRouteById($id) {
            $query = "select * from routes where id=$id";
            $resultSet = $this->db->query($query);
            return $resultSet->fetchAll();
        }

        public function insertSampleRoutes() {
            $query = "INSERT INTO routes 
                                    (source, dest, start, end, price) 
                                    VALUE ('Cork','London', '2014-01-01','2014-01-31',100)";
            $count = $this->db->exec($query);

            $query = "INSERT INTO routes 
                                    (source, dest, start, end, price) 
                                    VALUE ('Cork','Berlin', '2014-02-01','2014-02-28',100)";
            $count = $this->db->exec($query);
        }

        public function displayRouteManagementList() {
            $query = "select DISTINCT * from routes";
            $resultSet = $this->db->query($query);
            echo "Delete routes \t\t (click to edit) <br>";
            echo " Y\tN <br>";

            for ($i = 0; $i < $resultSet->rowCount(); $i++) {
                $route = $resultSet->fetch();
                $dummy = "route #" . $route['id'] . "  " . $route['source'] . "  " . $route['dest'] . "  " . $route['start'] . "  " . $route['end'] . "  " . $route['price'] . " <br>";
                $dummy1 = "r" . $i;
                echo "<input type=\"radio\" name=\"$dummy1\" value=\"Y\" >";
                echo "<input type=\"radio\"name=\"$dummy1\" value=\"N\" checked>";
                echo "<a href=\"controller.php?cmd=showEditRoute&amp;routeid={$route['id']}\"> $dummy       </a><br>";
            }
            return $resultSet;
        }

        public function displayRouteDetails() {
            $query = "select * from routes";
            $resultSet = $this->db->query($query);
            for ($i = 0; $i < $resultSet->rowCount(); $i++) {
                $routes = $resultSet->fetch();
                echo " <textarea name=\"textarea\" ROWS=6 COLS=50>";
                echo "\n  Source:   {$routes['source']}";
                echo "\n    Dest:   {$routes['dest']}";
                echo "\n   Start:   {$routes['start']}";
                echo "\n     End:   {$routes['end']}";
                echo "\n   Price:   {$routes['price']}";
                echo " </textarea>";
            }
        }

        public function displayBookedFlights($info) {
            $query = "select DISTINCT route,date from passangers order by date";
            $resultSet = $this->db->query($query);

            for ($i = 0; $i < $resultSet->rowCount(); $i++) {
                $passangers = $resultSet->fetch();
                $dummy = "{$passangers['route']} {$passangers['date']}";
                echo "<a href=\"controller.php?cmd=showFlightDetails&amp;routeid={$passangers['route']}&amp;dateid={$passangers['date']}\"> $dummy</a><br>";
            }
        }

        public function displayRouteForEditing($info) {
            $query = "select DISTINCT * from routes";
            $resultSet = $this->db->query($query);

            for ($i = 0; $i < $resultSet->rowCount(); $i++) {
                $route = $resultSet->fetch();
                if ($route['id'] == $info) {
                    echo " <textarea name=\"textarea\" ROWS=6 COLS=50>";
                    echo "Source:      {$route['source']}\nDestination: {$route['dest']}\nStart:       {$route['start']} \nEnd:         {$route['end']}";
                    echo " </textarea><br>";
                    echo "Edit pricing on route \t\t <br>";
                    echo "Price: <input type=\"text\" name=\"firstname\" value=\" {$route['price']}\"><br>";
                }
            }
        }

        function deleteSelectedRoutes() {
            $query = "select * from routes";
            $resultSet = $this->db->query($query);

            for ($i = 0; $i < $resultSet->rowCount(); $i++) {
                $route = $resultSet->fetch();
                if (isset($_POST['r' . $i]) && $_POST['r' . $i] == "Y") {
                    $dummy1 = $route['id'];
                    $query1 = "delete routes from routes where id = $dummy1";
                    $resultSet1 = $this->db->query($query1);
                }
            }
        }

        public function createRoute($source, $destination, $fromDate, $toDate, $price) {
            $date1 = $fromDate->format('Y-m-d');
            $date2 = $toDate->format('Y-m-d');

            $query = "INSERT INTO routes 
                                (source, dest, start, end, price) 
                                    VALUE ('$source','$destination','$date1','$date2',$price)";
            $count = $this->db->exec($query);
        }

        public function countRoutes() {
            $query = "select * from routes";
            $resultSet = $this->db->query($query);
            return $resultSet->rowCount();
        }

        public function displaySavedCustomerDetails() {
            $query = "select * from user";
            $resultSet = $this->db->query($query);

            for ($i = 0; $i < $resultSet->rowCount(); $i++) {
                $customer = $resultSet->fetch();

                if (isset($_COOKIE['username']) && $_COOKIE['username'] == $customer['username']) {
                    echo" <h1> Ticket/Purchasing Details </h1>
                            <form action=\"controller.php\" method=\"post\">
                            <input type=\"hidden\" name=\"cmd\" value=\"submitPassengerDetails\">
                            <table>
                            <tr>
                            <td>Name as per records:</td>
                            <td><input type=\"text\" name=\"username\" value=\"{$customer['username']}\"> </td>
                            </tr>
                            <tr>
                                <td>e-mail address:</td>
                                <td><input type=\"email\" name=\"email\" value=\"{$customer['email']}\"></td>
                            </tr>
                            <tr>
                                <td>Credit Card No:</td>
                                <td><input type=\"text\" name=\"CreditCardNo\" value=\"{$customer['CreditCardNo']}\"></td>
                            </tr>
                            <tr>
                                <td>Passport No:</td>
                                <td><input type=\"text\" name=\"PassportNo\" value=\"{$customer['passport']}\"></td>
                            </tr>
                            <tr>
                                <td><input type=\"submit\" value=\"Submit\"></td>
                                <td></td>
                            </tr> 
                    </div>
                    </form>
                    ";
                }
            }
        }

        public function recordPassengerBooking($username, $email, $CreditCardNo, $PassportNo, $route, $date, $seatNo) {
            $hasCookie = 0;
            $registerName = $_SESSION['username'];

            if (isset($_COOKIE['username'])) {
                $hasCookie = 1;
                $registerName = $_COOKIE['username'];
            } else {
                $registerName = $_SESSION['username'];
                $hasCookie = 0;
            }

            echo "<br> username:" . $username;
            echo "<br> pp: " . $PassportNo;
            echo "<br> route:" . $route;
            echo "<br> date:" . $date;
            echo "<BR> hascookie" . $hasCookie;
            echo "<br> Registered Name: " . $registerName;
            echo "<br> Credit Card " . $CreditCardNo;
            echo "<br> email :" . $email;
            echo "<br> Seat Number: b" . $seatNo;

            $query = "INSERT INTO passangers (name, passport,route,date,registered,registeredName,creditCardno,email,seatNo)
                                VALUE ('$username','$PassportNo',{$route},'$date',{$hasCookie},'$registerName','$CreditCardNo','$email',{$seatNo} )";
            $this->db->exec($query);
            echo "<br><a href=\"controller.php\">Back to search</a>";
        }

        public function displaySeatOccupancy($date) {
            $seats = array(
                0, 0, 0, 0, 0,
                0, 0, 0, 0, 0,
                0, 0, 0, 0, 0,
                0, 0, 0, 0, 0
            );
            $Total = 0;
            $query1 = "select * from passangers ";
            $resultSet = $this->db->query($query1);
            $Size = $resultSet->rowCount();

            echo " <textarea name=\"textarea\" ROWS=6 COLS=50>";
            echo "\nDate";
            echo "\t\tRoute";
            echo "\t\t    seat Occupied";
            for ($i = 0; $i < $Size; $i++) {
                $result = $resultSet->fetch();
                if (($result['date'] == $date) && ($result['route'] == $_SESSION['route'])) {
                    echo "\n" . $result['date'];
                    echo "\t" . $result['route'];
                    echo "\t\t\t" . $result['seatNo'];
                    $dummy = $result['seatNo'];
                    $seats[$dummy] = 1;
                    $Total++;
                }
            }

            if ($Total == 0) {
                $seatAvailable = 0;
                echo "\n\n Empty set - any seat is yours";
            }
            if ($Total == 20) {
                echo " \n\n Flight full";
                $seatAvailable = 20;
            } else {
                $seatsAvailable = 20 - $Total;
            }

            echo "\n\n Seats available:    {$seatsAvailable} ";
            echo " </textarea>";
            echo "<br><br> Seats available:    {$seatsAvailable} ";
        }

        public function calculateAvailableSeats($date, $nmrSeats, &$seats, &$freeSeats) {
            $Total = 0;
            $query1 = "select * from passangers ";
            $resultSet = $this->db->query($query1);
            $Size = $resultSet->rowCount();

            for ($i = 0; $i < $Size; $i++) {
                $result = $resultSet->fetch();
                if (($result['date'] == $date) && ($result['route'] == $_SESSION['route'])) {
                    $dummy = $result['seatNo'];
                    $seats[$dummy] = "Y";
                    $Total++;
                }
            }
            $freeSeats = 20 - $Total;
        }

        public function displayFlightManifestDetails($date, $route) {
            echo "<br> Route: " . $route;
            echo "<br> Date:  " . $date;
            $query = "select * from routes where id={$route} ";
            $resultSet1 = $this->db->query($query);
            $prices1 = $resultSet1->fetch();
            $price = $prices1['price'];
            $flight = $prices1['source'] . "  -  " . $prices1['dest'];

            $query1 = "select * from passangers where date='$date' and route={$route} order by '$date'";
            $resultSet = $this->db->query($query1);
            $nmrCustomers = $resultSet->rowCount();
            echo "<br> Revenue for flight " . $price * $nmrCustomers;
            echo "<br> From '$flight' ";
            echo "<BR> Prices Per Ticket €" . $price;
            echo "<br> Total No. of Customers" . $nmrCustomers;
            for ($i = 0; $i < $nmrCustomers; $i++) {
                $passenger = $resultSet->fetch();
                echo "<br>" . $passenger['seatNo'] . "&nbsp;&nbsp;&nbsp;&nbsp;" . $passenger['name'] . "&nbsp;&nbsp;&nbsp;&nbsp;" . $passenger['passport'] . "&nbsp;&nbsp;&nbsp;&nbsp;" . $passenger['creditCardno'] . "&nbsp;&nbsp;&nbsp;&nbsp;" . $passenger['email'] . "&nbsp;&nbsp;&nbsp;&nbsp;" . $passenger['registered'] . "&nbsp;&nbsp;&nbsp;&nbsp;" . $passenger['registeredName'];
            }
        }
    }
?>