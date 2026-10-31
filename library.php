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
    }

    // public function menu($info = false) {
    //     $this->renderCustomerMenu(false);
    // }

    // protected function renderCustomerMenu($loggedIn) {
    //     $canBook = $loggedIn && isset($_SESSION['route'], $_SESSION['date'], $_SESSION['seatCount']);
    //     echo '<nav id="menu" class="old" aria-label="Customer navigation">
    //         <div class="menu-left">
    //             <a href="controller.php">Home</a>
    //         </div>
    //         <div class="menu-right">';
    //     if ($loggedIn) {
    //         echo '<a href="controller.php?cmd=logout">Logout</a>';
    //     } else {
    //         echo '<a href="controller.php?cmd=showLogin">Login</a>
    //               <a href="controller.php?cmd=showRegistration">Register</a>';
    //     }
    //     if ($canBook) {
    //         echo '<a class="book-flight-cta" href="controller.php?cmd=showPassengerDetails">Book Flight</a>';
    //     } else {
    //         echo '<span class="book-flight-cta is-disabled" aria-disabled="true">Book Flight</span>';
    //     }
    //     echo '</div></nav>';
    // }

    public function menu($info = false) {
        $this->renderCustomerMenu(false);
    }

    protected function renderCustomerMenu($loggedIn) {
        echo '<nav id="menu" class="old" aria-label="Main navigation">
            <div class="menu-left">
                <a href="controller.php">Home</a>
            </div>
            <div class="menu-right">
                <a href="controller.php?cmd=showCheckout">Cart</a>
                <a href="controller.php?cmd=showPassengerDetails">Checkout</a>';

        if ($loggedIn) {
            echo '<a href="controller.php?cmd=logout">Logout</a>';
        } else {
            echo '<a href="controller.php?cmd=showLogin">Login / Register</a>';
        }

        echo '</div></nav>';
    }

    protected function renderIdentity() {
        $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
        echo '<div id="identity">' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . '</div>';
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
 * PublicHomeView (guest) and CustomerView (logged-in).
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

    // protected function detailsPane($selected = null) {
    //     echo '<section class="search-card" aria-labelledby="details-title">';
    //     echo '<h2 id="details-title">Flight details</h2>';

    //     if (!$selected) {
    //         echo '<p>Select a flight to see its details and add it to your cart.</p>';
    //     } else {
    //         echo '<p><strong>' . esc($selected['airline']) . '</strong> · Flight ' . esc($selected['flight_code']) . '</p>';
    //         echo '<p>' . esc($selected['from']) . ' → ' . esc($selected['to']) . '<br>';
    //         echo esc($selected['date']) . ', ' . esc($selected['departs']) . ' – ' . esc($selected['arrives']);
    //         if (!empty($selected['duration'])) {
    //             echo ' (' . esc($selected['duration']) . ')';
    //         }
    //         echo '</p>';
    //         echo '<p>Fare ' . esc($selected['fare']) . ' × ' . (int)(isset($_SESSION['seatCount']) ? $_SESSION['seatCount'] : 1);
    //         echo ' = <strong>' . esc($selected['total']) . '</strong></p>';

    //         echo '<form action="controller.php" method="post">';
    //         echo '<input type="hidden" name="cmd" value="addFlightToCart">';
    //         echo '<input type="hidden" name="flightDate" value="' . esc($selected['date']) . '">';
    //         echo '<input type="hidden" name="flightId" value="' . (int)$selected['flight_id'] . '">';
    //         echo '<input type="submit" value="Add to Cart">';
    //         echo '</form>';
    //     }

    //     // if (isset($_SESSION['cart'])) {
    //     //     $_SESSION['cart']->displayCart();
    //     // }
    //     echo '</section>';
    // }
    protected function detailsPane($selected = null) {
        echo '<section class="search-card flight-details-card" aria-labelledby="details-title">';
        echo '<h2 id="details-title">Flight details</h2>';

        if (!$selected) {
            echo '<p>Select a flight to see its details and add it to your cart.</p>';
        } else {
            $flightId = (int)$selected['flight_id'];

            $isInCart = isset($_SESSION['cartAddedFlightIds'][$flightId]);

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

    // public function __construct() {
    //     $this->top(false);
    //     $this->menu(false);
    //     $this->renderIdentity();
    //     $this->content();
    //     $this->bottom();
    // }

    // public function content() {
    //     $this->renderWorkspace(false); // no checkout link for guests
    // }
    // public function __construct($flights = null) {
    //     $this->top(false);
    //     $this->menu(false);
    //     $this->renderIdentity();
    //     $this->content($flights);
    //     $this->bottom();
    // }

    // public function content($flights = null) {
    //     $this->renderWorkspace(false, $flights);
    // }
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
    }

    protected function renderIdentity() {
        $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
        if ($username !== 'Guest') {
            echo '<div id="identity">' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . '</div>';
        }
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
            <div class="menu-left">
                <a href="controller.php">Home</a>
            </div>
            <div class="menu-right">
                <a href="controller.php?cmd=showCheckout">Cart</a>
                <a href="controller.php?cmd=showPassengerDetails">Checkout</a>';

        if ($loggedIn) {
            echo '<a href="controller.php?cmd=logout">Logout</a>';
        } else {
            echo '<a href="controller.php?cmd=showLogin">Login / Register</a>';
        }

        echo '</div></nav>';
    }
}

abstract class AdminBaseView extends PrivateBaseView {
    public function menu($info = false) {
        echo '<nav id="menu" class="old" aria-label="Administrator navigation">
            <a href="controller.php">Public home</a>
            <a href="controller.php?cmd=showAdminDashboard">Routes</a>
            <a href="controller.php?cmd=showAddRoute">Add route</a>
            <a href="controller.php?cmd=showFlightManifest">View manifest</a>
            <a href="controller.php?cmd=logout">Logout</a>
        </nav>';
        $this->renderIdentity();
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

class Cart {

    private $cartElements = array();
    private $cartID = 0;

    function addFlight($ticketInfo) {
        $this->cartElements[$this->cartID] = $ticketInfo;
        $this->cartID++;
    }

    function removeFlight($cartID) {
        unset($this->cartElements[$cartID]);
    }

    function recordBookings($username, $email, $CreditCardNo, $PassportNo) {
        $DBM = new DBManager();
        $cartIds = array_keys($this->cartElements);

        for ($i = 0; $i < sizeof($cartIds); $i++) {
            $cartID = $cartIds[$i];
            $nmrSeats = $this->cartElements[$cartID]['seatCount'];
            $seatNumbers = $this->cartElements[$cartID]['seatCount'];

            for ($j = 0; $j < 20; $j++) {
                if ("{$this->cartElements[$cartID]['seats'][$j]}" == "U") {
                    $DBM->recordPassengerBooking(
                        $username,
                        $email,
                        $CreditCardNo,
                        $PassportNo,
                        $this->cartElements[$cartID]['route'],
                        $this->cartElements[$cartID]['date'],
                        $j
                    );
                    $this->cartElements[$cartID]['seats'][$j] = "Y";
                    $seatNumbers--;
                }
                if ($seatNumbers == 0)
                    break;
            }

            for ($i = 0; $i < sizeof($cartIds); $i++) {
                unset($this->cartElements[$cartID]);
            }
        }
    }

    function displayCart() {
        $cartIds = array_keys($this->cartElements);

        for ($i = 0; $i < sizeof($cartIds); $i++) {
            $cartID = $cartIds[$i];

            echo "<p style=\"border:red solid 1px\">";
            echo "Route: " . $this->cartElements[$cartID]['route'] . "<br>";
            echo "date: " . $this->cartElements[$cartID]['date'] . "<br>";
            echo "Number of seats: " . $this->cartElements[$cartID]['seatCount'] . "<br>";
            echo "Occupied Seats:  " . $this->cartElements[$cartID]['freeSeats'] . "<br>";
            echo "<BR> First {$this->cartElements[$cartID]['seatCount']} seats selected will be chosen <BR>";

            $count = 0;

            for ($j = 0; $j < 20; $j++) {
                $dummy1 = $i . "b" . $j;
                echo $dummy1;

                if (($this->cartElements[$cartID]['seats'][$j] == "Y") ||
                    ($this->cartElements[$cartID]['seats'][$j] == "U")) {
                    echo "<input type=\"radio\" name=\"$dummy1\" value=\"Y\" checked >";
                    echo "<input type=\"radio\"name=\"$dummy1\" value=\"N\"disabled>";
                    $count++;
                } else {
                    echo "<input type=\"radio\" name=\"$dummy1\" value=\"Y\" >";
                    echo "<input type=\"radio\"name=\"$dummy1\" value=\"N\" checked>";
                }
                echo "<br>";
            }

            if ($count == 20)
                echo "<br> ---- FLIGHT BOOKED OUT -- ";

            echo '<a href="controller.php?cmd=removeCartItem&cartID=' . $cartID . '">delete</a>';
            echo "</p>";
        }

        echo "<br><a href=\"controller.php\">Back to search</a>";
        echo "</p>";
    }

    function processSeatSelections() {
        $cartIds = array_keys($this->cartElements);

        for ($i = 0; $i < sizeof($cartIds); $i++) {
            $cartID = $cartIds[$i];
            $nmrSeats = $this->cartElements[$cartID]['seatCount'];

            for ($j = 0; $j < 20; $j++) {
                $dummy1 = $i . "b" . $j;

                if ($this->cartElements[$cartID]['seats'][$j] == "Y") {
                    // Existing occupied seat: leave it unchanged.
                } else {
                    if (isset($_POST[$dummy1]) && $_POST[$dummy1] == "Y") {
                        $this->cartElements[$cartID]['seats'][$j] = "U";
                        $nmrSeats--;
                    }
                    if ($nmrSeats == 0)
                        break;
                }
            }
        }
    }
}

// Renders the flight search form (logged-in customer).
class CustomerView extends CustomerBaseView {
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
class CustomerFlightResultsView extends CustomerView {

    public function __construct($route, $seatCount, $date, $info, $flights = null, $selectedId = null) {
        $_SESSION['route']     = $route;
        $_SESSION['seatCount'] = $seatCount;
        $_SESSION['date']      = $date;

        parent::__construct($info, $flights, $selectedId);
    }
}

/**
 * Displays the passenger purchasing-details page.
 */
class CustomerPassengerDetailsView extends CustomerView {

    public function __construct($hasCookie) {
        $this->top($hasCookie);
        $this->menu($hasCookie);
        $this->getUserdetails($hasCookie);
        $this->bottom();
    }

    public function getUserdetails($hasCookie) {
        $hasCookie = false;
        if (isset($_COOKIE['username'])) {
            $hasCookie = true;
        }

        echo "<div id=\"content\">";
        if (!$hasCookie) {
            echo" <h1> Ticket/Purchasing Details </h1>
                    <form action=\"controller.php\" method=\"post\">
                <input type=\"hidden\" name=\"cmd\" value=\"submitPassengerDetails\">
                <table>
                    <tr>
                        <td>Name to be printed on ticket:</td>
                        <td><input type=\"text\" name=\"username\"> </td>
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
        } else {
            echo "we need to reteive data from db,display for editing";
            $DBM = new DBManager();
            $customerDetails = $DBM->displaySavedCustomerDetails();
        }
        echo "</div>";
    }
}

/**
 * Displays the checkout page.
 */
class CustomerCheckoutView extends CustomerView {

    public function __construct($info) {
        $this->top($info);
        $this->menu($info);
        $this->searchDiv();
        $this->bottom();
    }

    function searchDiv() {
        echo "<div id=\"content\">
                    <form action=\"controller.php\" method=\"post\">
            
                    <input type=\"hidden\" name=\"cmd\" value=\"updateCartSeats\">
                    <table>
                        <tr>
                        <td> </td>
                    </tr>";
        $_SESSION['cart']->displayCart();

        echo"   <tr>
            <td><input type=\"submit\" value=\"Seat Changes\"></td>
                </tr>
            </div>
            </form>
            ";
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
            $this->db = new PDO($address, $username, $password);
        } catch (PDOException $e) {
            $error_message = $e->getMessage();
            echo $error_message;
            exit();
        }
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