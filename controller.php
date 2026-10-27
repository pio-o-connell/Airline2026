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
                new InnerAdminView();
            } else {
                new FlightSearchView($hasCookie);
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
                new FlightSearchView($hasCookie);
            }
            break;

        case "addFlightToCart":
            handleAddFlightToCart();
            break;

        case "updateCartSeats":
            $_SESSION['cart']->processSeatSelections();
            new PassengerDetailsView($hasCookie);
            break;

        case "showCheckout":
            new CheckoutView($hasCookie);
            break;

        case "submitPassengerDetails":
            if (!requireCustomer()) {
                break;
            }
            handlePassengerDetailsSubmission();
            break;

        case "removeCartItem":
            $_SESSION['cart']->removeFlight($_GET['cartID']);
            new CheckoutView($hasCookie);
            break;

        case "showPassengerDetails":
            if (!requireCustomer()) {
                break;
            }
            new PassengerDetailsView($hasCookie);
            break;

        case "registerUser":
            handleUserRegistration($hasCookie);
            new FlightSearchView($hasCookie);
            break;

        case "showEditRoute":
            if (!requireAdministrator()) {
                break;
            }

            $routeid = $_GET['routeid'];
            new EditRouteView($routeid);
            break;

        case "updateRoute":
            if (!requireAdministrator()) {
                break;
            }

            handleRoutePriceUpdate(
                $_POST['firstname'],
                $_POST['index']
            );

            new InnerAdminView();
            break;

        case "deleteSelectedRoutes":
            if (!requireAdministrator()) {
                break;
            }

            handleRouteDeletion();
            new InnerAdminView();
            break;

        case "showAddRoute":
            if (!requireAdministrator()) {
                break;
            }

            new AddRouteView($hasCookie);
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

            new AddRouteView($hasCookie);
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

            new InnerAdminView();
            break;

        case "viewdate":
            $date = $_GET['dateid'];

            new FlightSearchResultsView(
                $_SESSION['route'],
                $_SESSION['seatCount'],
                $_SESSION['date'],
                $hasCookie
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
                $_SESSION['date']
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

function handleFlightSearch($hasCookie)
{
    $_SESSION['route'] = $_POST['route'];
    $_SESSION['seatCount'] = $_POST['seatCount'];
    $_SESSION['date'] = $_POST['date'];

    try {
        $yrdata = strtotime($_POST['date']);
        $input = date('d-m-Y', $yrdata);
        $date_format = 'd-m-Y';
        $time = strtotime($input);
        $is_valid = date($date_format, $time) == $input;

        if (!$is_valid) {
            new FlightSearchView($hasCookie);
            echo "<br> Valid Dates e.g.'01-Jan-2014'only";
        } elseif (!is_numeric($_POST['seatCount'])) {
            new FlightSearchView($hasCookie);
            echo "<br> Not numeric value entered";
        } elseif (
            !(($_POST['seatCount'] > 0)
            && ($_POST['seatCount'] < 21))
        ) {
            new FlightSearchView($hasCookie);
            echo "<br> Value between 1 and 20";
        } else {
            new FlightSearchResultsView(
                $_POST['route'],
                $_POST['seatCount'],
                $_POST['date'],
                $hasCookie
            );
        }
    } catch (Exception $e) {
        new FlightSearchView($hasCookie);
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

        new FlightSearchView($hasCookie);

        echo "Added to Cart provisional purchases- edit as see fit";
    } else {
        new FlightSearchView($hasCookie);

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

        new AddRouteView(1);
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

    new FlightManifestView($hasCookie);
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

        new InnerAdminView();

    } elseif ($result == "user") {
        setcookie(
            'username',
            $_POST['username'],
            time() + 3600,
            '/'
        );

        $_SESSION['username'] = $_POST['username'];

        new FlightSearchView(true);

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
