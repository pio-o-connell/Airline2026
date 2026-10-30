<?php

include("library.php");
session_start();

dispatchRequest();

function dispatchRequest()
{
    $cmd = "";

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
            if (!isset($_SESSION['username'])) {
                $_SESSION['username'] = "Guest";
            }

            if ($_SESSION['username'] === "Guest") {
                new PublicHomeView();
            } elseif ($_SESSION['username'] === "rmiller") {
                new AdminView();
            } else {
                new CustomerView($hasCookie);
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
                new CustomerView($hasCookie);
            }
            break;

        case "selectFlight":
            if (empty($_SESSION['route'])) {
                new CustomerView($hasCookie);
                break;
            }
            new CustomerFlightResultsView(
                $_SESSION['route'],
                $_SESSION['seatCount'],
                $_SESSION['date'],
                $hasCookie,
                loadFlightResults(),
                (int)(isset($_GET['flightid']) ? $_GET['flightid'] : 0)
            );
            break;

        case "addFlightToCart":
            handleAddFlightToCart();
            break;

        case "updateCartSeats":
            $_SESSION['cart']->processSeatSelections();
            new CustomerPassengerDetailsView($hasCookie);
            break;

        case "showCheckout":
            new CustomerCheckoutView($hasCookie);
            break;

        case "submitPassengerDetails":
            if (!requireCustomer()) {
                break;
            }
            handlePassengerDetailsSubmission();
            break;

        case "removeCartItem":
            $_SESSION['cart']->removeFlight($_GET['cartID']);
            new CustomerCheckoutView($hasCookie);
            break;

        case "showPassengerDetails":
            if (!requireCustomer()) {
                break;
            }
            new CustomerPassengerDetailsView($hasCookie);
            break;

        case "registerUser":
            handleUserRegistration($hasCookie);
            new CustomerView($hasCookie);
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

function requireAdministrator()
{
    if (
        isset($_SESSION['username'])
        && $_SESSION['username'] === "rmiller"
    ) {
        return true;
    }

    new LoginView(false);
    return false;
}

function requireCustomer()
{
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

function isValidDate($date, $format = 'd-m-Y H:i:s')
{
    $d = DateTime::createFromFormat($format, $date);

    return $d && $d->format($format) == $date;
}

/**
 * Calculate flight duration using airport time zones.
 */
function calculateDuration($date, $depart, $arrive, $fromCity, $toCity)
{
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
function prepareFlightResults($rows, $seatCount)
{
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
function loadFlightResults()
{
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

function handleFlightSearch($hasCookie)
{
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
            new CustomerView($hasCookie);
            echo "<br> Valid Dates e.g.'01-Jan-2014'only";
        } elseif (!is_numeric($_POST['seatCount'])) {
            new CustomerView($hasCookie);
            echo "<br> Not numeric value entered";
        } elseif (
            !(($_POST['seatCount'] > 0)
            && ($_POST['seatCount'] < 21))
        ) {
            new CustomerView($hasCookie);
            echo "<br> Value between 1 and 20";
        } else {
            new CustomerFlightResultsView(
                $_POST['route'],
                $_POST['seatCount'],
                $_POST['date'],
                $hasCookie,
                loadFlightResults()
            );
        }
    } catch (Exception $e) {
        new CustomerView($hasCookie);
        echo "<br> Valid Dates e.g.'01-Jan-2014'only";
    }
}

function handleAddFlightToCart()
{
    $flightInfo = array();

    $flightInfo['route'] = $_SESSION['route'];
    $flightInfo['seatCount'] = $_SESSION['seatCount'];
    $flightInfo['date'] = $_POST['flightDate'];

    $seats = array_fill(0, 20, "N");
    $freeSeats = 0;

    $DBM = new DBManager();

    $DBM->calculateAvailableSeats(
        $flightInfo['date'],
        $flightInfo['seatCount'],
        $seats,
        $freeSeats
    );

    $flightInfo['seats'] = $seats;
    $flightInfo['freeSeats'] = $freeSeats;

    $hasCookie = isset($_COOKIE['username']);

    if ($freeSeats >= $_SESSION['seatCount']) {
        $_SESSION['cart']->addFlight($flightInfo);

        new CustomerView($hasCookie, loadFlightResults());

        echo "Added to Cart provisional purchases- edit as see fit";
    } else {
        new CustomerView($hasCookie, loadFlightResults());

        echo "Only $freeSeats available -- not added to cart";
    }
}

function handleUserRegistration(&$hasCookie)
{
    $DBM = new DBManager();

    return $DBM->registerUser($hasCookie);
}

function handleRoutePriceUpdate($value, $index)
{
    $DBM = new DBManager();

    $DBM->updateRoutePrice($value, $index);
}

function handleRouteDeletion()
{
    $DBM = new DBManager();

    $DBM->deleteSelectedRoutes();
}

function handleRouteCreation(
    $source,
    $destination,
    $operationalFrom,
    $operationalTo,
    $price
) {
    $DBM = new DBManager();
    $count = $DBM->countRoutes();

    if ($count > 4) {
        echo "Max. number of routes entered";
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
    } catch (Exception $e) {
        echo "Date entered in incorrect format e.g. 01-Jan-2014";

        new AdminAddRouteView(1);
    }
}

function handlePassengerDetailsSubmission()
{
    $_SESSION['cart']->recordBookings(
        $_POST['username'],
        $_POST['email'],
        $_POST['CreditCardNo'],
        $_POST['PassportNo']
    );

    echo '<br><a href="controller.php">Back to search</a>';
}

function handleFlightManifestRequest($hasCookie)
{
    echo "<br> View Manifest";

    new AdminFlightManifestView($hasCookie);
}

function handleUserAuthentication()
{
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

        new AdminView();

    } elseif ($result == "user") {
        setcookie(
            'username',
            $_POST['username'],
            time() + 3600,
            '/'
        );

        $_SESSION['username'] = $_POST['username'];

        new CustomerView(true);

    } else {
        echo "<br> invalid login <br>";

        $_SESSION['username'] = "Guest";

        new PublicHomeView();
    }
}

function handleSeatOccupancyRequest($date)
{
    $DBM = new DBManager();

    return $DBM->displaySeatOccupancy($date);
}

function handleCartProcessing()
{
    $DBM = new DBManager();

    return $DBM->processCart();
}

function handleFlightDetailsRequest($date, $route)
{
    $DBM = new DBManager();

    return $DBM->displayFlightManifestDetails($date, $route);
}

?>