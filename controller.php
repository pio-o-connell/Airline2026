<?php

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();

include("library.php");
session_start();

dispatchRequest();

function dispatchRequest()
{
    $cmd = "";

    if (!isset($_SESSION['username'])) {
        $_SESSION['username'] = "Guest";
    }

    if (($_SESSION['username'] ?? "Guest") === "Guest"
        && isset($_SESSION['booking_owner'])) {
        $_SESSION['cart'] = new Cart();
        unset($_SESSION['booking_owner']);
    }

    if (($_SESSION['username'] ?? "Guest") !== "Guest"
        && ($_SESSION['username'] ?? "Guest") !== "rmiller"
        && !isset($_SESSION['booking_owner'])) {
        $_SESSION['booking_owner'] = $_SESSION['username'];
    }

    $hasCookie = isset($_SESSION['username'])
        && $_SESSION['username'] !== "Guest";

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = new Cart();
    }

    if (isset($_POST['cmd'])) {
        $cmd = $_POST['cmd'];
    } elseif (isset($_GET['cmd'])) {
        $cmd = $_GET['cmd'];
    }

    switch ($cmd) {
        case "":
            if ($_SESSION['username'] === "Guest") {
                new PublicHomeView();
            } elseif ($_SESSION['username'] === "rmiller") {
                new AdminView();
            } else {
                new CustomerHomeView($hasCookie);
            }
            break;

        case "showLogin":
            new LoginView($hasCookie);
            break;

        case "showRegistration":
            new RegistrationView();
            break;

        case "authenticateUser":
            handleUserAuthentication();
            break;

        case "searchFlights":
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                handleFlightSearch($hasCookie);
            } else {
                new CustomerHomeView($hasCookie);
            }
            break;

        case "selectFlight":
            if (empty($_SESSION['route'])) {
                if (($_SESSION['username'] ?? "Guest") === "Guest") {
                    new PublicHomeView();
                } else {
                    new CustomerHomeView($hasCookie);
                }
                break;
            }
            $flights = loadFlightResults();
            $selectedId = (int)(isset($_GET['flightid']) ? $_GET['flightid'] : 0);
            if (($_SESSION['username'] ?? "Guest") === "Guest") {
                new PublicHomeView($flights, $selectedId);
            } else {
                new CustomerFlightResultsView(
                    $_SESSION['route'],
                    $_SESSION['seatCount'],
                    $_SESSION['date'],
                    $hasCookie,
                    $flights,
                    $selectedId
                );
            }
            break;

        case "addFlightToCart":
            handleAddFlightToCart();
            break;
      
        case "showSeatSelection":
            if (($_SESSION['username'] ?? "Guest") === "Guest") {
                new GuestSeatSelectionView();
            } else {
                new CustomerSeatSelectionView($hasCookie);
            }
            break;
       
        case "updateCartSeats":
            $cart = $_SESSION['cart'] ?? null;
            $valid = ($cart !== null);

            if ($valid) {
                $cartFlights = $cart->getCartElements();
                $submittedSelections = $_POST['seatSelections'] ?? array();

                foreach ($cartFlights as $flightId => $flight) {

                    $selections = $submittedSelections[$flightId] ?? null;

                    if (!$cart->updateSeatSelections($flightId, $selections)) {
                        $valid = false;
                        break;
                    }
                }
            }

            if ($valid) {
                $valid = $cart->processSeatSelections();
            }

            if (!$valid) {
                FlashMessage::add('warning', 'Please select the required number of available seats for every flight.');

                if (($_SESSION['username'] ?? "Guest") === "Guest") {
                    new GuestSeatSelectionView();
                } else {
                    new CustomerSeatSelectionView($hasCookie);
                }
                break;
            }

            header('Location: controller.php?cmd=showPassengerDetails');
            exit();


        case "submitPassengerDetails":
            handlePassengerDetailsSubmission();
            break;

        case "startStripeCheckout":
            handleStripeCheckout();
            break;

        case "stripeSuccess":
            handleStripeSuccess();
            break;

        case "stripeCancel":
            FlashMessage::add('info', 'Payment was cancelled. Your Cart has been retained.');
            if (($_SESSION['username'] ?? 'Guest') === 'Guest') {
                new GuestCheckoutView();
            } else {
                new CustomerCartView($hasCookie);
            }
            break;
       
        case "showPassengerDetails":
            $cart = $_SESSION['cart'] ?? null;

            if (!$cart || !$cart->processSeatSelections()) {
                if (($_SESSION['username'] ?? "Guest") === "Guest") {
                    new GuestSeatSelectionView();
                } else {
                    new CustomerSeatSelectionView($hasCookie);
                }
            } elseif (($_SESSION['username'] ?? "Guest") === "Guest") {
                new GuestCheckoutView();
            } else {
                new CustomerCheckoutView($hasCookie);
            }
            break;


        case "showCheckout":
            if (($_SESSION['username'] ?? "Guest") === "Guest") {
                new GuestCartView();
            } else {
                new CustomerCartView($hasCookie);
            }
            break;


        case "removeCartItem":
            $flightId = (int)($_GET['flightId'] ?? 0);

            if ($flightId > 0) {
                $_SESSION['cart']->removeFlight($flightId);
            }

            if (($_SESSION['username'] ?? "Guest") === "Guest") {
                new GuestCartView();
            } else {
                new CustomerCartView($hasCookie);
            }
            break;

        case "registerUser":
            $guestCart = ($_SESSION['username'] ?? 'Guest') === 'Guest'
                ? ($_SESSION['cart'] ?? new Cart())
                : null;
            handleUserRegistration($hasCookie);
            session_regenerate_id(true);
            restoreCustomerBookingState($_SESSION['username'], $guestCart);
            resumeAuthenticatedBooking();
            break;

        case "showEditRoute":
            if (!requireAdministrator()) {
                break;
            }

            $routeid = $_GET['routeid'];
            new AdminEditRouteView($routeid);
            break;

        case "updateRoute":
            if (!requireAdministrator()) {
                break;
            }

            handleRoutePriceUpdate(
                $_POST['firstname'],
                $_POST['index']
            );

            new AdminView();
            break;

        case "deleteSelectedRoutes":
            if (!requireAdministrator()) {
                break;
            }

            handleRouteDeletion();
            new AdminView();
            break;

        case "showAddRoute":
            if (!requireAdministrator()) {
                break;
            }

            new AdminAddRouteView($hasCookie);
            break;

        case "createRoute":
            if (!requireAdministrator()) {
                break;
            }

            handleRouteCreation(
                $_POST['source'],
                $_POST['destination'],
                $_POST['operationalFrom'],
                $_POST['operationalTo'],
                $_POST['Price']
            );

            new AdminAddRouteView($hasCookie);
            break;

        case "showFlightManifest":
            if (!requireAdministrator()) {
                break;
            }

            handleFlightManifestRequest($hasCookie);
            break;

        case "showFlightDetails":
            if (!requireAdministrator()) {
                break;
            }

            $date = $_GET['dateid'];
            $route = $_GET['routeid'];

            handleFlightDetailsRequest($date, $route);
            break;

        case "showAdminDashboard":
            if (!requireAdministrator()) {
                break;
            }

            new AdminView();
            break;

        case "viewdate":
            $date = $_GET['dateid'];

            new CustomerFlightResultsView(
                $_SESSION['route'],
                $_SESSION['seatCount'],
                $_SESSION['date'],
                $hasCookie,
                loadFlightResults()
            );

            handleSeatOccupancyRequest($date);
            break;

        case "logout":
            $username = $_SESSION['username'] ?? 'Guest';
            if ($username !== 'Guest' && $username !== 'rmiller') {
                $db = new DBManager();
                $db->saveCustomerBookingState(
                    $username,
                    $_SESSION['cart'] ?? new Cart()
                );
            }

            if (isset($_COOKIE['username'])) {
                setcookie(
                    'username',
                    '',
                    time() - 3600,
                    '/'
                );

                unset($_COOKIE['username']);
            }

            $_SESSION['username'] = "Guest";
            $_SESSION['cart'] = new Cart();
            unset($_SESSION['booking_owner']);

            unset(
                $_SESSION['route'],
                $_SESSION['seatCount'],
                $_SESSION['date'],
                $_SESSION['trip_type']
            );

            new PublicHomeView();
            break;
    }
}

function restoreCustomerBookingState($username, $guestCart = null){
    $db = new DBManager();
    $savedCart = $db->loadCustomerBookingState($username);

    if ($savedCart && !empty($savedCart->getCartElements())) {
        if ($guestCart instanceof Cart
            && !empty($guestCart->getCartElements())) {
            FlashMessage::add(
                'info',
                'Your saved account booking was restored. The temporary guest Cart was not merged.'
            );
        }
        $_SESSION['cart'] = $savedCart;
    } elseif ($guestCart instanceof Cart
        && !empty($guestCart->getCartElements())) {
        $_SESSION['cart'] = $guestCart;
        $db->saveCustomerBookingState($username, $guestCart);
    } else {
        $_SESSION['cart'] = new Cart();
    }

    $_SESSION['booking_owner'] = $username;
}

function resumeAuthenticatedBooking(){
    $returnCommand = $_SESSION['booking_return_command'] ?? '';
    unset($_SESSION['booking_return_command']);

    if ($returnCommand === 'showPassengerDetails') {
        new CustomerCheckoutView(true);
        return;
    }

    if ($returnCommand === 'showCheckout') {
        new CustomerCartView(true);
        return;
    }

    $cart = $_SESSION['cart'] ?? null;
    if ($cart instanceof Cart && !empty($cart->getCartElements())) {
        if ($cart->hasCompletePassengerDetails()) {
            new CustomerCheckoutView(true);
        } else {
            new CustomerCartView(true);
        }
        return;
    }

    new CustomerHomeView(true);
}

function requireAdministrator(){
    if (
        isset($_SESSION['username'])
        && $_SESSION['username'] === "rmiller"
    ) {
        return true;
    }

    new LoginView(false);
    return false;
}

function requireCustomer(){
    if (
        isset($_SESSION['username'])
        && $_SESSION['username'] !== "Guest"
        && $_SESSION['username'] !== "rmiller"
    ) {
        return true;
    }

    new LoginView(false);
    return false;
}

function isValidDate($date, $format = 'd-m-Y H:i:s'){
    $d = DateTime::createFromFormat($format, $date);

    return $d && $d->format($format) == $date;
}

/**
 * Calculate flight duration using airport time zones.
 */
function calculateDuration($date, $depart, $arrive, $fromCity, $toCity){
    try {
        $tz   = AIRPORT_TIMEZONES;
        $from = isset($tz[strtolower($fromCity)]) ? $tz[strtolower($fromCity)] : 'UTC';
        $to   = isset($tz[strtolower($toCity)])   ? $tz[strtolower($toCity)]   : $from;

        $dep  = new DateTime("$date $depart", new DateTimeZone($from));
        $arr  = new DateTime("$date $arrive", new DateTimeZone($to));

        // Overnight arrival
        if ($arr <= $dep) {
            $arr->modify('+1 day');
        }

        $mins = (int)(($arr->getTimestamp() - $dep->getTimestamp()) / 60);
        return $mins > 0 ? ((int)($mins / 60)) . 'h ' . ($mins % 60) . 'm' : null;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Turn raw DB rows into the array structure the views expect.
 */
function prepareFlightResults($rows, $seatCount){
    $out = array();
    foreach ($rows as $r) {
        $out[] = array(
            'flight_id'   => (int)$r['flight_id'],
            'airline'     => AIRLINE_NAME,
            'logo'        => AIRLINE_LOGO,
            'flight_code' => $r['flight_code'],
            'date'        => $r['flight_date'],
            'from'        => ucwords($r['source']),
            'to'          => ucwords($r['dest']),
            'departs'     => substr($r['departure_time'], 0, 5),
            'arrives'     => substr($r['arrival_time'], 0, 5),
            'duration'    => calculateDuration(
                $r['flight_date'],
                $r['departure_time'],
                $r['arrival_time'],
                $r['source'],
                $r['dest']
            ),
            'fare'        => CURRENCY . number_format($r['price']),
            'total'       => CURRENCY . number_format($r['price'] * (int)$seatCount),
        );
    }
    return $out;
}

/**
 * Load flights for the current search (±2 days around the chosen date).
 * Returns null when no search has been performed yet.
 */
function loadFlightResults(){
    if (empty($_SESSION['route']) || empty($_SESSION['date']) || empty($_SESSION['seatCount'])) {
        return null; // not searched yet
    }

    try {
        $start = new DateTime($_SESSION['date']);
    } catch (Exception $e) {
        return array();
    }

    $DBM  = new DBManager();
    $from = clone $start;
    $to   = clone $start;
    $from->modify('-2 days');
    $to->modify('+2 days');

    $rows = $DBM->getFlightsForSearch(
        $_SESSION['route'],
        $from->format('Y-m-d'),
        $to->format('Y-m-d')
    );

    return prepareFlightResults($rows, $_SESSION['seatCount']);
}

function handleFlightSearch($hasCookie){
    $_SESSION['route']     = $_POST['route'];
    $_SESSION['seatCount'] = $_POST['seatCount'];
    $_SESSION['date']      = $_POST['date'];
    $_SESSION['trip_type'] = isset($_POST['trip_type']) ? $_POST['trip_type'] : 'oneway';

    try {
        $yrdata = strtotime($_POST['date']);
        $input = date('d-m-Y', $yrdata);
        $date_format = 'd-m-Y';
        $time = strtotime($input);
        $is_valid = date($date_format, $time) == $input;

        if (!$is_valid) {
            FlashMessage::add('error', "Valid Dates e.g. '01-Jan-2014' only.");
            new CustomerHomeView($hasCookie);
        } elseif (!is_numeric($_POST['seatCount'])) {
            FlashMessage::add('error', 'Not numeric value entered.');
            new CustomerHomeView($hasCookie);
        } elseif (
            !(($_POST['seatCount'] > 0)
            && ($_POST['seatCount'] < 21))
        ) {
            FlashMessage::add('error', 'Value between 1 and 20.');
            new CustomerHomeView($hasCookie);
        } else {
  
            $flights = loadFlightResults();

            if ($_SESSION['username'] === "Guest") {
                new PublicHomeView($flights);
            } else {
                new CustomerFlightResultsView(
                    $_SESSION['route'],
                    $_SESSION['seatCount'],
                    $_SESSION['date'],
                    $hasCookie,
                    $flights
                );
}
        }
    } catch (Exception $e) {
        FlashMessage::add('error', "Valid Dates e.g. '01-Jan-2014' only.");
        new CustomerHomeView($hasCookie);
    }
}


function handleAddFlightToCart(){

    $hasCookie = isset($_COOKIE['username']);
    $isGuest = (($_SESSION['username'] ?? "Guest") === "Guest");

    $flightId = (int)($_POST['flightId'] ?? 0);
    $passengerCount = (int)($_SESSION['seatCount'] ?? 0);

    $DBM = new DBManager();

    // Validate the submitted flight and passenger count.
    if ($flightId <= 0 || $passengerCount < 1 || $passengerCount > 20) {
        $message = 'Invalid flight or passenger quantity.';
        $selectedId = 0;
        FlashMessage::add('error', $message);
    } else {

        // Retrieve the actual flight details from the database.
        $flight = $DBM->getFlightForCart($flightId);

        if (!$flight) {
            $message = 'The selected flight could not be found.';
            $selectedId = 0;
            FlashMessage::add('error', $message);

        } elseif ((int)$flight['route_id'] !== (int)$_SESSION['route']) {
            $message = 'The selected flight does not match your search.';
            $selectedId = 0;
            FlashMessage::add('warning', $message);

        } else {

            $flightInfo = array(
                'flight_id' => $flightId,
                'route_id' => (int)$flight['route_id'],
                'route' => $_SESSION['route'],
                'flight_code' => $flight['flight_code'],
                'aircraft_code' => $flight['aircraft_code'],
                'date' => $flight['flight_date'],
                'from' => ucwords($flight['source']),
                'to' => ucwords($flight['dest']),
                'departs' => substr($flight['departure_time'], 0, 5),
                'arrives' => substr($flight['arrival_time'], 0, 5),
                'seatCount' => $passengerCount,
                'unitFare' => (float)$flight['price'],
                'totalFare' => (float)$flight['price'] * $passengerCount,
                'seatSelections' => array()
            );

            // Add the flight or increase its existing passenger quantity.
            $added = $_SESSION['cart']->addFlight($flightInfo);

            if ($added) {
                $message = 'Flight added to Cart.';
                $selectedId = $flightId;
                FlashMessage::add('success', $message);

            } else {
                $message = 'Unable to add flight. Maximum passenger limit reached.';
                $selectedId = $flightId;
                FlashMessage::add('warning', $message);
            }
        }
    }

    // Redisplay the flight results.
    $flights = loadFlightResults();

    if ($isGuest) {
        new PublicHomeView($flights, $selectedId);
    } else {
        new CustomerFlightResultsView(
            $_SESSION['route'],
            $_SESSION['seatCount'],
            $_SESSION['date'],
            $hasCookie,
            $flights,
            $selectedId
        );
    }
}

function handleUserRegistration(&$hasCookie){
    $DBM = new DBManager();

    return $DBM->registerUser($hasCookie);
}

function handleRoutePriceUpdate($value, $index){
    $DBM = new DBManager();

    $DBM->updateRoutePrice($value, $index);
}

function handleRouteDeletion(){
    $DBM = new DBManager();

    $DBM->deleteSelectedRoutes();
}

function handleRouteCreation($source,$destination,$operationalFrom,$operationalTo,$price){
    $DBM = new DBManager();
    $count = $DBM->countRoutes();

    if ($count > 4) {
        FlashMessage::add('warning', 'Max. number of routes entered.');
        return;
    }

    try {
        $fromDate = new DateTime($operationalFrom);
        $toDate = new DateTime($operationalTo);

        $DBM->createRoute(
            $source,
            $destination,
            $fromDate,
            $toDate,
            $price
        );
        } 
        catch (Exception $e) {
            FlashMessage::add('error', 'Date entered in incorrect format e.g. 01-Jan-2014');

            new AdminAddRouteView(1);
        }
}


function handlePassengerDetailsSubmission(){
    if (
        !isset($_POST['passengers']) ||
        !is_array($_POST['passengers'])
    ) {
        FlashMessage::add('warning', 'No passenger details were submitted.');
        return;
    }

    $cart = $_SESSION['cart'] ?? null;

    if (!$cart) {
        FlashMessage::add('warning', 'Your Cart is empty.');
        return;
    }

    if (!$cart->updatePassengers($_POST['passengers'])) {
        FlashMessage::add('error', 'Unable to update passenger details.');
        return;
    }

    if (($_SESSION['username'] ?? 'Guest') === 'Guest') {
        new GuestCheckoutView();
    } else {
        new CustomerCheckoutView(true);
    }
}

function handleStripeCheckout(){
    $cart = $_SESSION['cart'] ?? null;

    if (($_SESSION['username'] ?? 'Guest') === 'Guest') {
        $_SESSION['booking_return_command'] = 'showCheckout';
        FlashMessage::add('info', 'Please log in or register before continuing to payment.');
        new GuestCheckoutView();
        return;
    }

    if (!$cart || empty($cart->getCartElements())
        || !$cart->hasCompletePassengerDetails()
        || !$cart->processSeatSelections()) {
        FlashMessage::add('warning', 'Complete passenger details and seat selection before payment.');
        if (($_SESSION['username'] ?? 'Guest') === 'Guest') {
            new GuestCheckoutView();
        } else {
            new CustomerCheckoutView(true);
        }
        return;
    }

    try {
        $gateway = new StripeGateway();
        $baseUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost:8000');
        $successUrl = $baseUrl . '/controller.php?cmd=stripeSuccess';
        $cancelUrl = $baseUrl . '/controller.php?cmd=stripeCancel';
        $session = $gateway->createCheckoutSession($cart, $successUrl, $cancelUrl);
        $_SESSION['stripe_checkout_session_id'] = $session['id'];
        header('Location: ' . $session['url']);
        exit();
    } catch (Throwable $e) {
        error_log('Stripe checkout failed: ' . $e->getMessage());
        FlashMessage::add('error', 'Unable to start payment. Please try again later.');
        if (($_SESSION['username'] ?? 'Guest') === 'Guest') {
            new GuestCheckoutView();
        } else {
            new CustomerCheckoutView(true);
        }
    }
}

function handleStripeSuccess(){
    $sessionId = trim((string)($_GET['session_id'] ?? ''));

    try {
        $gateway = new StripeGateway();
        $stripeSession = $gateway->retrieveCheckoutSession($sessionId);

        if (($stripeSession['payment_status'] ?? '') !== 'paid'
            || ($stripeSession['status'] ?? '') !== 'complete') {
            throw new RuntimeException('Stripe has not confirmed this payment.');
        }

        $db = new DBManager();
        $existing = $db->getBookingByPaymentSession($sessionId);
        if ($existing) {
            $_SESSION['cart'] = new Cart();
            if (($_SESSION['username'] ?? 'Guest') !== 'Guest') {
                $db->deleteCustomerBookingState($_SESSION['username']);
            }
            new BookingConfirmationView($existing['booking_reference']);
            return;
        }

        $cart = $_SESSION['cart'] ?? null;
        if (!$cart instanceof Cart) {
            throw new RuntimeException('The booking Cart is no longer available.');
        }

        $result = $cart->recordBookings(
            $_SESSION['username'] ?? 'Guest',
            $sessionId
        );

        if (!$result['success']) {
            throw new RuntimeException($result['error']);
        }

        $_SESSION['cart'] = new Cart();
        unset($_SESSION['stripe_checkout_session_id']);
        if (($_SESSION['username'] ?? 'Guest') !== 'Guest') {
            $db->deleteCustomerBookingState($_SESSION['username']);
        }
        new BookingConfirmationView($result['reference']);
    } catch (Throwable $e) {
        error_log('Stripe booking confirmation failed: ' . $e->getMessage());
        FlashMessage::add('error', 'Payment was received, but booking confirmation failed.');
        FlashMessage::add('warning', 'Your Cart has been retained. Please contact support before retrying payment.');
        if (($_SESSION['username'] ?? 'Guest') === 'Guest') {
            new GuestCheckoutView();
        } else {
            new CustomerCheckoutView(true);
        }
    }
}

function handleFlightManifestRequest($hasCookie){
    echo "<br> View Manifest";

    new AdminFlightManifestView($hasCookie);
}

function handleUserAuthentication(){
    $guestCart = ($_SESSION['username'] ?? 'Guest') === 'Guest'
        ? ($_SESSION['cart'] ?? new Cart())
        : null;
    $DBM = new DBManager();

    $result = $DBM->authenticateUser(
        $_POST['username'],
        $_POST['password']
    );

    if ($result == "rmiller") {
        setcookie(
            'username',
            $_POST['username'],
            time() + 3600,
            '/'
        );

        $_SESSION['username'] = "rmiller";
        $_SESSION['cart'] = new Cart();
        unset($_SESSION['booking_owner']);

        new AdminView();

    } elseif ($result == "user") {
        setcookie(
            'username',
            $_POST['username'],
            time() + 3600,
            '/'
        );

        session_regenerate_id(true);
        $_SESSION['username'] = $_POST['username'];
        restoreCustomerBookingState($_SESSION['username'], $guestCart);
        resumeAuthenticatedBooking();

    } else {
        FlashMessage::add('error', 'Invalid login.');

        $_SESSION['username'] = "Guest";

        new PublicHomeView();
    }
}

function handleSeatOccupancyRequest($date){
    $DBM = new DBManager();

    return $DBM->displaySeatOccupancy($date);
}

function handleCartProcessing(){
    $DBM = new DBManager();

    return $DBM->processCart();
}

function handleFlightDetailsRequest($date, $route){
    $DBM = new DBManager();

    return $DBM->displayFlightManifestDetails($date, $route);
}

?>