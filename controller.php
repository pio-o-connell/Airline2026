<?php

    include("library.php");

    session_start();

    // Main controller for handling requests and routing them to views or processing functions.
    dispatchRequest();

    // Selects the requested action and displays the appropriate view or processes the request.
    function dispatchRequest() {

        $cmd = "";

        $hasCookie = false;
        //      echo $_COOKIE['username'];
        if (isset($_COOKIE['username'])) {
            // only create the cart when the session starts
            $hasCookie = true;
        }

        if (!isset($_SESSION['cart'])) {
            // only create the cart when the session starts
            $_SESSION['cart'] = new Cart();
        }

        if (isset($_POST['cmd'])) {
            $cmd = $_POST['cmd'];
        } else if (isset($_GET['cmd'])) {
            $cmd = $_GET['cmd'];
        }

        //       echo "cmd".$cmd;
        switch ($cmd) {
            case "":
                if ($hasCookie) {
                    if (($_COOKIE['username']) == "rmiller")
                        new AdminRoutesView($hasCookie);
                    else
                        new FlightSearchView($hasCookie);
                }
                else {
                    $_SESSION['username'] = "Guest";
                    new FlightSearchView($hasCookie);
                }
                break;

            case "showLogin":
                new LoginView($hasCookie);
                break;

            case "showRegistration":
                new RegistrationView($hasCookie);
                break;

            case "authenticateUser":
                handleUserAuthentication();
                break;

            case "searchFlights":
                // The navigation link opens the form with GET; only submitted
                // search forms should read the POST search fields.
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
                handlePassengerDetailsSubmission();
                break;

            case "removeCartItem":
                $_SESSION['cart']->removeFlight($_GET['cartID']);
                new CheckoutView($hasCookie);
                break;

            case "showPassengerDetails":
                new PassengerDetailsView($hasCookie);
                break;

            case "registerUser":
                handleUserRegistration($hasCookie);
                $hasCookie = 1;
                new FlightSearchView($hasCookie);
                break;

            case "showEditRoute":
                $routeid = $_GET['routeid'];
                new EditRouteView($routeid);
                break;

            case "updateRoute":
                handleRoutePriceUpdate($_POST['firstname'], $_POST['index']);
                new AdminRoutesView($hasCookie);
                break;

            case "deleteSelectedRoutes":
                handleRouteDeletion();
                new AdminRoutesView($hasCookie);
                break;

            case "showAddRoute":
                new AddRouteView($hasCookie);
                break;

            case "createRoute":
                handleRouteCreation($_POST['source'], $_POST['destination'], $_POST['operationalFrom'], $_POST['operationalTo'], $_POST['Price']);
                new AddRouteView($hasCookie);
                break;

            case "showFlightManifest":
                handleFlightManifestRequest($hasCookie);
                break;

            case "showFlightDetails":
                $date = $_GET['dateid'];
                $route = $_GET['routeid'];
                handleFlightDetailsRequest($date, $route);
                break;

            case "showAdminDashboard":
                new AdminRoutesView($hasCookie);
                break;

            case "viewdate":
                $date = $_GET['dateid'];
                new FlightSearchResultsView($_SESSION['route'], $_SESSION['seatCount'], $_SESSION['date'], $hasCookie);
                handleSeatOccupancyRequest($date);
                break;

            case "logout":
                if (isset($_COOKIE['username'])) {
                    setcookie('username', false, time() - 60 * 100000, '/'); // empty value and old timestamp
                    unset($_COOKIE['username']);
                }
                $_SESSION['username'] = "Guest";
                $hasCookie = false;
                new FlightSearchView($hasCookie);
                break;
        }
    }

    // Checks whether a date matches the specified format.
    function isValidDate($date, $format = 'd-m-Y H:i:s') {

        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    // Validates the flight search inputs and displays the search results if valid.
    function handleFlightSearch($hasCookie) {
        $_SESSION['route'] = $_POST['route'];
        $_SESSION['seatCount'] = $_POST['seatCount'];
        $_SESSION['date'] = $_POST['date'];

        try {
            $yrdata = strtotime($_POST['date']);
            $input = date('d-m-Y', $yrdata);

            $date_format = 'd-m-Y';
            $time = strtotime($input);
            $is_valid = date($date_format, $time) == $input;

            //  print "Valid? ".($is_valid ? 'yes' : 'no');

            if (!$is_valid) {
                new FlightSearchView($hasCookie);
                echo "<br> Valid Dates e.g.'01-Jan-2014'only";
            } else {
                if (!is_numeric($_POST['seatCount'])) {
                    new FlightSearchView($hasCookie);
                    echo "<br> Not numeric value entered";
                } else {
                    if (!(($_POST['seatCount'] > 0 ) && ($_POST['seatCount'] < 21))) {
                        new FlightSearchView($hasCookie);
                        echo "<br> Value between 1 and 20";
                    } else
                        new FlightSearchResultsView($_POST['route'], $_POST['seatCount'], $_POST['date'], $hasCookie);
                }
            }
        } catch (Exception $e) {
            echo "<br> Valid Dates e.g.'01-Jan-2014'only";
        }
    }

    // Checks seat availability and adds the selected flight to the session cart if enough seats are available.
    function handleAddFlightToCart() {
        $flightInfo = array();
        $flightInfo['route'] = $_SESSION['route'];
        $flightInfo['seatCount'] = $_SESSION['seatCount'];
        $flightInfo['date'] = $_POST['flightDate'];

        $seats = array(
            "N", "N", "N", "N", "N",
            "N", "N", "N", "N", "N",
            "N", "N", "N", "N", "N",
            "N", "N", "N", "N", "N"
        );

        $freeSeats = 0;
        $DBM = new DBManager();
        $DBM->calculateAvailableSeats($flightInfo['date'], $flightInfo['seatCount'], $seats, $freeSeats);
        $flightInfo['seats'] = $seats;
        $flightInfo['freeSeats'] = $freeSeats;

        $hasCookie = false;
        if (isset($_COOKIE['username']))
            $hasCookie = true;

        if ($freeSeats >= $_SESSION['seatCount']) {
            new FlightSearchView($hasCookie);
            echo "Added to Cart provisional purchases- edit as see fit";
            $_SESSION['cart']->addFlight($flightInfo);
        } else {
            new FlightSearchView($hasCookie);
            echo "Only $freeSeats available -- not added to cart";
        }
    }

    // Registers a new user through the database manager.
    function handleUserRegistration(&$hasCookie) {
        $DBM = new DBManager();
        $data = $DBM->registerUser($hasCookie);

        return $data;
    }

    // Updates the price of an existing route.
    function handleRoutePriceUpdate($value, $index) {
        $DBM = new DBManager();
        $DBM->updateRoutePrice($value, $index);
    }

    // Deletes the routes selected for removal.
    function handleRouteDeletion() {
        $DBM = new DBManager();
        $DBM->deleteSelectedRoutes();
    }

    // Validates the route operating dates and adds a new route if the route limit has not been reached.
    function handleRouteCreation($source, $destination, $operationalFrom, $operationalTo, $price) {
        $DBM = new DBManager();

        $count = $DBM->countRoutes();

        if ($count > 4) {
            echo "Max. number of routes entered";
        } else {
            try {
                $fromDate = new DateTime($operationalFrom);
                $toDate = new DateTime($operationalTo);
                $DBM->createRoute($source, $destination, $fromDate, $toDate, $price);
            } catch (Exception $e) {
                echo "Date entered in incorrect format e.g. 01-Jan-2014";
                new AddRouteView(1);
            }
        }
        //    $DBM->createRoute($source,$destination,$operationalFrom,$operationalTo,$price);
        //    $DBM->createRoute();
    }

    // Updates the cart's transaction details with the submitted customer information.
    function handlePassengerDetailsSubmission() {
        //echo "processing anonymous user details /n";
        $_SESSION['cart']->recordBookings($_POST['username'], $_POST['email'], $_POST['CreditCardNo'], $_POST['PassportNo']);
        echo "<br><a href=\"controller.php\">Back to search</a>";
    }

    // Displays the administrator's flight manifest.
    function handleFlightManifestRequest($hasCookie) {
        echo "<BR> View Manifest";
        new FlightManifestView($hasCookie);
    }

    // Authenticates the user and displays the appropriate administrator or customer view.
    function handleUserAuthentication() {

        $DBM = new DBManager();

        $result = $DBM->authenticateUser($_POST['username'], $_POST['password']);

        if ($result == "rmiller") {
            setCookie('username', $_POST['username'], time() + 3600, '/');
            $hasCookie = 1;           // shimmy
            $_SESSION['username'] = "rmiller";
            new AdminRoutesView($hasCookie);
        } else if ($result == "user") {
            setCookie('username', $_POST['username'], time() + 3600, '/');
            $_SESSION['username'] = $_POST['username'];
            $hasCookie = 1;            //shimmy
            new FlightSearchView($hasCookie);
        } else {
            echo "<br> invalid login <br>";
            $hasCookie = 0;           // shimmy
            $_SESSION['username'] = "Guest";
            new FlightSearchView($hasCookie);
        }
    }

    // Retrieves and displays seat occupancy information for the specified date.
    function handleSeatOccupancyRequest($date) {
        $DBM = new DBManager();
        $result = $DBM->displaySeatOccupancy($date);
    }

    // Requests cart processing through the database manager.
    function handleCartProcessing() {
        $DBM = new DBManager();
        $result = $DBM->processCart();
    }

    // Retrieves and displays flight and passenger details for the specified date and route.
    function handleFlightDetailsRequest($date, $route) {
        $DBM = new DBManager();
        $result = $DBM->displayFlightManifestDetails($date, $route);
    }

?>
