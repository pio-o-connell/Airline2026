<?php

abstract class OutsideView {

    // Shared public-page shell. Keep the original IDs for legacy markup/CSS.
    function top($info = false) {
        echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Airlines</title>
    <link rel="stylesheet" href="mystyles.css">
</head>
<body class="public-page">
    <div id="main" class="old">
        <header id="header" class="old">
            <h1>PHP Airlines.com</h1>
            <p class="tagline">Your journey starts here</p>
        </header>
';
    }

    public function menu($info = false) {
        echo '
        <nav id="menu" class="old" aria-label="Main navigation">
            <a href="controller.php">Home</a>
            <a href="controller.php?cmd=search">Search flights</a>
            <a href="controller.php?cmd=login">Login</a>
            <a href="controller.php?cmd=register">Register</a>
            <a href="controller.php?cmd=login">Staff / admin</a>
        </nav>
';
    }

    public function bloggerList() {
        // Retained for compatibility with older views that call this method.
        echo '\n        <aside id="bloggerlist" class="old"></aside>\n';
    }

    function bottom() {
        echo '
        <footer id="footer" class="old">
            <span>PHP Airlines.com</span>
            <a href=\"controller.php\">Public home</a>
        </footer>
    </div>
</body>
</html>
';
    }
}

class InsideView {

    public function __construct($info) {
        $this->top();
        $this->menu();
        $this->content($info);
        $this->bloggerList();





        $this->bottom();
    }

    public function content($info) {

        echo " 

	<div id=\"content\">
                  <h1> Add a post!!</h1>
                  <FORM METHOD=\"post\" ACTION=\"controller.php\">

                 
                        <input type=\"hidden\" name=\"cmd\" value=\"addPost\">
                       // <textarea>
                       <textarea name=\"textarea\" ROWS=6 COLS=50>
                        Add post here!
                        </textarea>

                            
        
                <input type=\"submit\" value=\"addPost\">
            </FORM>
              
                
            </div>
            ";
    }

    function top() {


        echo "
            
            <html>

            <head>

	<link rel=\"stylesheet\" href=\"insidestyles.css\">
            </head>

            <body>

	<div id=\"main\">
		<div id=\"header\">
			<h1>Header</h1>
		</div>
            
            ";
    }

    public function menu() {
        echo "
                 <div id=\"menu\">
                <a href=\"controller.php\">Public home</a> &nbsp;
                <a href=\"controller.php?cmd=login\">Add</a> <br>               
                       <a href=\"controller.php?cmd=register\">Delete</a><br>
                       <a href=\"controller.php?cmd=register\">logout</a><br>
                       
            </div>


            ";
    }

    public function bloggerList() {


        echo "
                 <div id=\"bloggerlist\">               
                    <h3> blogger list </h3>";
        //echo "in bloggerlist";
        // $DBM = new DBManager();
        //  $DBM->retrieveblogs();
        // get the blogs from the database
        /*               $query = "select * from post";
          $resultSet = $this->db->query($query);


          for($i=0;$i<$resultSet->rowCount();$i++){

          echo " here at least";
          $blogger = $resultSet->fetch();

          echo "<a href=\"controller.php?cmd=openblog\"> blogger['bloggerid']</a><br>";

          //           if ($username == $blogger['username']){

          }
         */


        echo "</div>";
    }

    function bottom() {
        echo "
            </div>

            </body>
            </html>        
            ";
    }

}

class loginView extends OutsideView {

    public function __construct($info) {
        $this->top($info);
        $this->menu($info);
        //     $this->bloggerList();


        $this->content();


        $this->bottom();
    }
    public function menu($info = false) {
        echo '<nav id="menu" class="old" aria-label="Main navigation">
            <a href="controller.php">Home</a>
            <a href="controller.php?cmd=search">Search flights</a>
            <a href="controller.php?cmd=login">Login</a>
            <a href="controller.php?cmd=register">Register</a>
            <a href="controller.php?cmd=login">Staff / admin</a>
        </nav>';
    }

    
    public function content() {

        echo "

	<div id=\"content\">
                  <h1> login </h1>
                   <FORM METHOD=\"post\" ACTION=\"controller.php\">

                 
                        <input type=\"hidden\" name=\"cmd\" value=\"loginRequest\">
                
                Username: <input type=\"text\" name=\"username\"> <br>
                Password: <input type=\"password\" name=\"password\"> <br>
                <input type=\"submit\"\ value=\"loginRequest\">
            </FORM>

                
            </div>
            ";
    }
    function top($info = false) {
        parent::top($info);
    }

    }

class adminView extends InsideView {

    public function __construct($info) {
        $this->top();
        $this->menu();
        //    $this->bloggerList();


        $this->content($info);
        $this->bloggerList();

        $this->bottom();
    }

    public function content($info) {
        echo "<div id=\"content\">
                  <h1> Administrator  -Edit/Delete Routes</h1>
                  <FORM METHOD=\"post\" ACTION=\"controller.php\">
                    <input type=\"hidden\" name=\"cmd\" value=\"deleteRoutes\">
                   
                    <input type=\"hidden\" name=\"blogtitle\" value=>";
        $DBM = new DBManager();
        $DBM->displayRoutes();

        echo"     <input type=\"submit\" value=\"delete routes\">
                    
            </FORM>
           ";

        echo "</div>";
    }

    function top() {


        echo "
            
            <html>

            <head>

	<link rel=\"stylesheet\" href=\"insidestyles.css\">
            </head>

            <body>

	<div id=\"main\">
		<div id=\"header\">
			<h1>Aireline.com</h1>
		</div>
            
            ";
    }

    public function menu() {
        echo "
                 <div id=\"menu\">
                <a href=\"controller.php\">Public home</a> &nbsp;             
                       <a href=\"controller.php?cmd=addRoutes\">Add <br> Route</a><br>
                       <a href=\"controller.php?cmd=manifest\">View<br> Manifest</a><br>
                   
                       <a href=\"controller.php?cmd=logout\">logout</a><br>
                       
            </div>


            ";
    }

    /*     <a href=\"controller.php?cmd=viewFlightDetails\">View<br> Flight<br> Details</a><br> */

    public function bloggerList() {
        
    }

    function bottom() {
        echo "
            </div>

            </body>
            </html>        
            ";
    }

}

class adminEditView extends InsideView {

    public function __construct($info) {
        $this->top();
        $this->menu();
        //    $this->bloggerList();


        $this->content($info);
        $this->bloggerList();

        $this->bottom();
    }

    public function content($info) {
        echo "<div id=\"content\">
                  <h1> Administrator  -Edit Route# {$info} </h1>
                  <FORM METHOD=\"post\" ACTION=\"controller.php\">
                    <input type=\"hidden\" name=\"cmd\" value=\"amendroute\">
                   
                    <input type=\"hidden\" name=\"index\" value=$info>";
        $DBM = new DBManager();
        $DBM->displayEditRoutes($info);

        echo"     <input type=\"submit\" value=\"editPrice\">
                    
            </FORM>
           ";

        echo "</div>";
    }

    function top() {


        echo "
            
            <html>

            <head>

	<link rel=\"stylesheet\" href=\"insidestyles.css\">
            </head>

            <body>

	<div id=\"main\">
		<div id=\"header\">
			<h1>Aireline.com</h1>
		</div>
            
            ";
    }

    public function menu() {
        echo "
                 <div id=\"menu\">
                <a href=\"controller.php\">Public home</a> &nbsp;             
                       <a href=\"controller.php?cmd=addRoutes\">Add <br> Route</a><br>
                       <a href=\"controller.php?cmd=suspendUsers\">View<br> Manifest</a><br>
                       <a href=\"controller.php?cmd=logout\">logout</a><br>
                       
            </div>


            ";
    }

    public function bloggerList() {
        
    }

    function bottom() {
        echo "
            </div>

            </body>
            </html>        
            ";
    }

}

class adminAddRoute extends InsideView {

    public function __construct($info) {
        $this->top();
        $this->menu();
        //    $this->bloggerList();


        $this->content($info);
        $this->bloggerList();

        $this->bottom();
    }

    public function content($info) {
        echo "<div id=\"content\">
                  <h1> Administrator  -Add Route# - max. 8 </h1>
                  <FORM METHOD=\"post\" ACTION=\"controller.php\">
                    <input type=\"hidden\" name=\"cmd\" value=\"addRoute\">
                   
                    <input type=\"hidden\" name=\"index\" value=$info>";
        $DBM = new DBManager();
        $DBM->displayStaticRoutes($info);
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
                        <td><input type=\"submit\" value=\"addRoute\"></td>
                        <td></td>

                    </tr> 
                    
                
            </div>
            </form>
            ";


        echo"  </FORM>
           ";

        echo "</div>";
    }

    function top() {


        echo "
            
            <html>

            <head>

	<link rel=\"stylesheet\" href=\"insidestyles.css\">
            </head>

            <body>

	<div id=\"main\">
		<div id=\"header\">
			<h1>Aireline.com</h1>
		</div>
            
            ";
    }

    public function menu() {
        echo "
                 <div id=\"menu\">
                <a href=\"controller.php\">Public home</a> &nbsp;             
                       <a href=\"controller.php?cmd=defaultAdmin\">Edit <br> Route</a><br>
                       <a href=\"controller.php?cmd=suspendUsers\">View<br> Manifest</a><br>
                       <a href=\"controller.php?cmd=logout\">logout</a><br>
                       
            </div>


            ";
    }

    public function bloggerList() {
        
    }

    function bottom() {
        echo "
            </div>

            </body>
            </html>        
            ";
    }

}

/*
  class adminFlightDetails extends InsideView{
  public function __construct($info){
  $this->top();
  $this->menu();
  //    $this->bloggerList();


  $this->content($info);
  $this->bloggerList();

  $this->bottom();


  }

  public function content($info){
  echo "<div id=\"content\">
  <h1> Administrator  -Add Route# - max. 8 </h1>
  <FORM METHOD=\"post\" ACTION=\"controller.php\">
  <input type=\"hidden\" name=\"cmd\" value=\"addRoute\">

  <input type=\"hidden\" name=\"index\" value=$info>";
  $DBM = new DBManager();
  $DBM->displayStaticRoutes($info);
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
  <td><input type=\"submit\" value=\"addRoute\"></td>
  <td></td>

  </tr>


  </div>
  </form>
  ";


  echo"  </FORM>
  ";

  echo "</div>";



  }



  function top(){


  echo "

  <html>

  <head>

  <link rel=\"stylesheet\" href=\"insidestyles.css\">
  </head>

  <body>

  <div id=\"main\">
  <div id=\"header\">
  <h1>Aireline.com</h1>
  </div>

  ";

  }

  public function menu(){
  echo "
  <div id=\"menu\">
  <a href=\"controller.php?cmd=defaultAdmin\">Edit <br> Route</a><br>
  <a href=\"controller.php?cmd=suspendUsers\">View<br> Manifest</a><br>
  <a href=\"controller.php?cmd=logout\">logout</a><br>

  </div>


  ";


  }

  public function bloggerList(){





  }

  function bottom(){
  echo "
  </div>

  </body>
  </html>
  ";
  }
  } */

class adminManifest extends InsideView {

    public function __construct($info) {
        $this->top();
        $this->menu();
        //    $this->bloggerList();


        $this->content($info);
        $this->bloggerList();

        $this->bottom();
    }

    public function content($info) {
        echo "<div id=\"content\">
                  <h1> Administrator  -Display flights by date # - </h1>
                  <FORM METHOD=\"post\" ACTION=\"controller.php\">
                    <input type=\"hidden\" name=\"cmd\" value=\"viewFlightdetails\">
                   
                    <input type=\"hidden\" name=\"index\" value=$info>";
        $DBM = new DBManager();
        $DBM->displayActiveRoutes($info);
        echo "<table>
                    <tr>
                        <td> 
                    <tr>
                        <td><input type=\"submit\" value=\"addRoute\"></td>
                        <td></td>

                    </tr> 
                    
                
            </div>
            </form>
            ";


        echo"  </FORM>
           ";

        echo "</div>";
    }

    function top() {


        echo "
            
            <html>

            <head>

	<link rel=\"stylesheet\" href=\"insidestyles.css\">
            </head>

            <body>

	<div id=\"main\">
		<div id=\"header\">
			<h1>Aireline.com</h1>
		</div>
            
            ";
    }

    public function menu() {
        echo "
                 <div id=\"menu\">
                <a href=\"controller.php\">Public home</a> &nbsp;             
                       <a href=\"controller.php?cmd=defaultAdmin\">Edit <br> Route</a><br>
                       <a href=\"controller.php?cmd=manifest\">View<br> Manifest</a><br>
                       <a href=\"controller.php?cmd=logout\">logout</a><br>
                       
            </div>


            ";
    }

    public function bloggerList() {
        
    }

    function bottom() {
        echo "
            </div>

            </body>
            </html>        
            ";
    }

}

class registerView extends OutsideView {

    public function __construct() {
        $info = 0;
        $this->top($info);
        $this->menu($info);
        //       $this->bloggerList();


        $this->content();


        $this->bottom();
    }
    public function menu($info = false) {
        echo '<nav id="menu" class="old" aria-label="Main navigation">
            <a href="controller.php">Home</a>
            <a href="controller.php?cmd=search">Search flights</a>
            <a href="controller.php?cmd=login">Login</a>
            <a href="controller.php?cmd=register">Register</a>
            <a href="controller.php?cmd=login">Staff / admin</a>
        </nav>';
    }

    
    public function content() {

        echo "

	<div id=\"content\">
                  <h1> Register </h1>
                    <form action=\"controller.php\" method=\"post\">
                <input type=\"hidden\" name=\"cmd\" value=\"registerRequest\">
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
    function top($info = false) {
        parent::top($info);
    }

    }

// class Cart {

//     private $cartElements = array();
//     private $cartID = 0;

//     // don't forget to remove from cart if undelete item,from process list


//     /**
//      * Adds a flight selection to the session cart.
//      *
//      * @param array $ticketInfo Flight details, including route, date,
//      *                          requested seat count and seat availability.
//      *
//      * Stores the selection under the next numeric cart ID, then
//      * increments the ID for the next item.
//      */
//     function addToCart($ticketInfo) {

//         // adding a numeric key to the cardElements array
//         $this->cartElements[$this->cartID] = $ticketInfo;
//         $this->cartID++;
//     }

//     function deleteFromCart($cartID) {
//         unset($this->cartElements[$cartID]);
//     }

//     /*
//       function updateTransactions($username,$email,$CreditCardNo,$PassportNo){
//       $DBM = new DBManager();

//       $cartIds = array_keys($this->cartElements);


//       for($i=0;$i<sizeof($cartIds);$i++){
//       $cartID = $cartIds[$i];
//       // another loop here
//       $nmrSeats=$this->cartElements[$cartID]['seatCount'];
//       $seatNumber=$this->cartElements[$cartID]['seatCount'];

//       $DBM->processTransaction($username,$email,$CreditCardNo,$PassportNo,$this->cartElements[$cartID]['route'],$this->cartElements[$cartID]['date'],$this->cartElements[$cartID]['seatCount']);
//       unset($this->cartElements[$cartID]);
//       }
//       }
//      */

//     //put in the form here for the loops
//     // put '2' in for the changes made by user
//     function updateTransactions($username, $email, $CreditCardNo, $PassportNo) {
//         $DBM = new DBManager();

//         $cartIds = array_keys($this->cartElements);


//         for ($i = 0; $i < sizeof($cartIds); $i++) {
//             $cartID = $cartIds[$i];
//             $nmrSeats = $this->cartElements[$cartID]['seatCount'];
//             $seatNumbers = $this->cartElements[$cartID]['seatCount'];
//             for ($j = 0; $j < 20; $j++) {
//                 //    echo "<br> checking cart setting".$this->cartElements[$cartID]['seats'][$j];
//                 if ("{$this->cartElements[$cartID]['seats'][$j]}" == "U") {
//                     $DBM->processTransaction($username, $email, $CreditCardNo, $PassportNo, $this->cartElements[$cartID]['route'], $this->cartElements[$cartID]['date'], $j);
//                     $this->cartElements[$cartID]['seats'][$j] = "Y";
//                     $seatNumbers--;
//                 }
//                 if ($seatNumbers == 0)
//                     break;
//             }
//             for ($i = 0; $i < sizeof($cartIds); $i++) {
//                 unset($this->cartElements[$cartID]);
//             }
//         }
//     }

//     function displayCart() {

//         $cartIds = array_keys($this->cartElements);


//         for ($i = 0; $i < sizeof($cartIds); $i++) {

//             $cartID = $cartIds[$i];


//             echo "<p style=\"border:red solid 1px\">";



//             echo "Route: " . $this->cartElements[$cartID]['route'] . "<br>";

//             echo "date: " . $this->cartElements[$cartID]['date'] . "<br>";
//             echo "Number of seats: " . $this->cartElements[$cartID]['seatCount'] . "<br>";


//             echo "Occupied Seats:  " . $this->cartElements[$cartID]['freeSeats'] . "<br>";
//             echo "<BR> First {$this->cartElements[$cartID]['seatCount']} seats selected will be chosen <BR>";
//             $count = 0;
//             for ($j = 0; $j < 20; $j++) {
//                 $dummy1 = $i . "b" . $j;
//                 echo $dummy1;
//                 $seats1 = $this->cartElements[$cartID]['seats'];
//                 //          if ($seats1[$j]=="Y")|| ($seats1[$j]=="U"))
//                 if (($this->cartElements[$cartID]['seats'][$j] == "Y") || ($this->cartElements[$cartID]['seats'][$j] == "U")) {

//                     echo "<input type=\"radio\" name=\"$dummy1\" value=\"Y\" checked >";
//                     echo "<input type=\"radio\"name=\"$dummy1\" value=\"N\"disabled>";
//                     $count++;
//                 } else {

//                     echo "<input type=\"radio\" name=\"$dummy1\" value=\"Y\" >";
//                     echo "<input type=\"radio\"name=\"$dummy1\" value=\"N\" checked>";
//                 }
//                 /* if(( $j % 4)==0) */
//                 echo "<br>";
//             }
//             if ($count == 20)
//                 echo "<br> ---- FLIGHT BOOKED OUT -- ";



//             echo '<a href="controller.php?cmd=delCheckoutItem&cartID=' . $cartID . '">delete</a>';


//             echo "</p>";
//         }
//         echo "<br><a href=\"controller.php\">Back to search</a>";
//         //      echo "<br><a href=\"controller.php?cmd=purchase\">Continue with purchase</a> &nbsp;";
//         //     echo '<a href="controller.php?cmd=delCheckoutItem&cartID='.$cartID.'">delete</a>';
//         //         print_r($this->cartElements); 
//         echo "</p>";
//     }

//     /* --------  PUT IN SUBMIT BUTTON HERE ---, LINK TO USER DETAILS */
//     /**
//      * Processes changes submitted from the checkout cart.
//      *
//      * Examines the submitted seat fields for each cart item.
//      * Newly selected seats are marked "U" in the session cart,
//      * and the remaining requested seat count is reduced.
//      *
//      * Existing seats marked "Y" are left unchanged.
//      *
//      * Note: This method updates the session cart; it does not
//      * itself confirm a reservation in the database.
//      */

//     function processFromCart() {

//         $cartIds = array_keys($this->cartElements);
//         //need to compare both clicked and updated    
//         for ($i = 0; $i < sizeof($cartIds); $i++) {
//             $cartID = $cartIds[$i];
//             $nmrSeats = $this->cartElements[$cartID]['seatCount'];

//             for ($j = 0; $j < 20; $j++) {
//                 $dummy1 = $i . "b" . $j;
//                 $seats1 = $this->cartElements[$cartID]['seats'];
//                 if ($this->cartElements[$cartID]['seats'][$j] == "Y") {
//                     //       echo " <br> not changing -already Y";
//                 } else {
//                     if ($_POST[$dummy1] == "Y") {
//                         // echo the newly set Y, first of the settings
//                         $this->cartElements[$cartID]['seats'][$j] = "U";


//                         $nmrSeats--;
//                     }
//                     if ($nmrSeats == 0)
//                         break;
//                 }
//                 //      print_r($this->cartElements);  
//             }
//         }
//     }

// }

    class Cart {

        // Stores flight selections using their numeric cart IDs.
        private $cartElements = array();

        // ID assigned to the next item added to the cart.
        private $cartID = 0;

        /**
         * Adds a flight selection to the cart.
         *
         * @param array $ticketInfo Flight details, including route, date,
         *                          seat count and seat information.
         *
         * Stores the supplied flight information under the current cart ID,
         * then increments the ID for the next item.
         */
        function addToCart($ticketInfo) {

            // Store the flight selection under a numeric key.
            $this->cartElements[$this->cartID] = $ticketInfo;

            // Prepare the next cart ID.
            $this->cartID++;
        }

        /**
         * Removes a flight selection from the cart.
         *
         * @param int $cartID ID of the cart item to remove.
         *
         * Removes the item from the in-memory cart array.
         */
        function deleteFromCart($cartID) {
            unset($this->cartElements[$cartID]);
        }

        /*
        //put in the form here for the loops
        // put '2' in for the changes made by user

        function updateTransactions($username,$email,$CreditCardNo,$PassportNo){
        $DBM = new DBManager();

        $cartIds = array_keys($this->cartElements);


        for($i=0;$i<sizeof($cartIds);$i++){
        $cartID = $cartIds[$i];
        // another loop here
        $nmrSeats=$this->cartElements[$cartID]['seatCount'];
        $seatNumber=$this->cartElements[$cartID]['seatCount'];

        $DBM->processTransaction($username,$email,$CreditCardNo,$PassportNo,$this->cartElements[$cartID]['route'],$this->cartElements[$cartID]['date'],$this->cartElements[$cartID]['seatCount']);
        unset($this->cartElements[$cartID]);
        }
        }
        */


        /**
         * Records the selected seats for every cart item as passenger
         * transactions in the database.
         *
         * @param string $username Passenger name or username.
         * @param string $email Passenger email address.
         * @param string $CreditCardNo Credit card number supplied to the method.
         * @param string $PassportNo Passport number supplied to the method.
         *
         * For each cart item, examines seats marked "U", records each
         * selected seat using DBManager::processTransaction(), and changes
         * the seat marker to "Y".
         *
         * Note: The current implementation contains a nested-loop issue
         * when removing cart items. Review before changing its behaviour.
         */
        function updateTransactions($username, $email, $CreditCardNo, $PassportNo) {
            $DBM = new DBManager();

            $cartIds = array_keys($this->cartElements);

            for ($i = 0; $i < sizeof($cartIds); $i++) {
                $cartID = $cartIds[$i];
                $nmrSeats = $this->cartElements[$cartID]['seatCount'];
                $seatNumbers = $this->cartElements[$cartID]['seatCount'];

                // Examine the 20 possible seats for this flight.
                for ($j = 0; $j < 20; $j++) {

                    // A seat marked "U" has been selected in the cart.
                    if ("{$this->cartElements[$cartID]['seats'][$j]}" == "U") {

                        // Record the selected seat in the database.
                        $DBM->processTransaction(
                            $username,
                            $email,
                            $CreditCardNo,
                            $PassportNo,
                            $this->cartElements[$cartID]['route'],
                            $this->cartElements[$cartID]['date'],
                            $j
                        );

                        // Mark the seat as processed and reduce the count.
                        $this->cartElements[$cartID]['seats'][$j] = "Y";
                        $seatNumbers--;
                    }

                    // Stop when the requested number of seats is processed.
                    if ($seatNumbers == 0)
                        break;
                }

                // Remove the cart item after processing.
                // NOTE: The inner loop reuses $i and is likely unintended.
                for ($i = 0; $i < sizeof($cartIds); $i++) {
                    unset($this->cartElements[$cartID]);
                }
            }
        }

        /**
         * Displays the current cart as HTML.
         *
         * For each flight, shows the route, date, requested seat count,
         * seat-selection controls and a link to remove the cart item.
         *
         * Seat controls use "Y" for selected/occupied and "N" for unselected.
         * Newly selected seats are represented internally by "U" after
         * processFromCart() handles the submitted form.
         */
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

                // Display the 20 seat options for this flight.
                for ($j = 0; $j < 20; $j++) {

                    // Field name used to identify a seat in the submitted form.
                    $dummy1 = $i . "b" . $j;
                    echo $dummy1;

                    $seats1 = $this->cartElements[$cartID]['seats'];

                    // Previously occupied or newly selected seats cannot be
                    // deselected using the disabled "N" option.
                    if (($this->cartElements[$cartID]['seats'][$j] == "Y") ||
                        ($this->cartElements[$cartID]['seats'][$j] == "U")) {

                        echo "<input type=\"radio\" name=\"$dummy1\" value=\"Y\" checked >";
                        echo "<input type=\"radio\"name=\"$dummy1\" value=\"N\"disabled>";
                        $count++;

                    } else {

                        // Allow an available seat to be selected or left empty.
                        echo "<input type=\"radio\" name=\"$dummy1\" value=\"Y\" >";
                        echo "<input type=\"radio\"name=\"$dummy1\" value=\"N\" checked>";
                    }

                    echo "<br>";
                }

                // Report when all 20 seats are marked occupied or selected.
                if ($count == 20)
                    echo "<br> ---- FLIGHT BOOKED OUT -- ";

                // Link to remove this flight from the cart.
                echo '<a href="controller.php?cmd=delCheckoutItem&cartID=' . $cartID . '">delete</a>';

                echo "</p>";
            }

            // Navigation back to the flight search.
            echo "<br><a href=\"controller.php\">Back to search</a>";
            echo "</p>";
        }

        /**
         * Processes the seat selections submitted from the checkout form.
         *
         * Examines the submitted fields for each cart item. Seats already
         * marked "Y" are left unchanged. Newly selected seats are marked
         * "U", and the remaining requested seat count is reduced.
         *
         * This updates the session cart only; it does not itself write
         * a booking to the database.
         */
        function processFromCart() {

            $cartIds = array_keys($this->cartElements);

            // Process the submitted seat selections for each cart item.
            for ($i = 0; $i < sizeof($cartIds); $i++) {
                $cartID = $cartIds[$i];
                $nmrSeats = $this->cartElements[$cartID]['seatCount'];

                // Check each of the 20 possible seats.
                for ($j = 0; $j < 20; $j++) {
                    $dummy1 = $i . "b" . $j;
                    $seats1 = $this->cartElements[$cartID]['seats'];

                    // Do not change seats already marked as occupied.
                    if ($this->cartElements[$cartID]['seats'][$j] == "Y") {
                        // Existing occupied seat: leave it unchanged.
                    } else {

                        // Mark a newly selected seat as "U".
                        if ($_POST[$dummy1] == "Y") {
                            $this->cartElements[$cartID]['seats'][$j] = "U";
                            $nmrSeats--;
                        }

                        // Stop once the requested number of seats is reached.
                        if ($nmrSeats == 0)
                            break;
                    }
                }
            }
        }
    }

    // class SearchView extends OutsideView {

    //     public function __construct($info) {

    //         $this->top($info);
    //         $this->menu($info);
    //         //    $this->bloggerList();
    //         $this->searchDiv();

    //         $this->bottom();
    //     }

    //     function searchDiv() {

    //         echo "<div id=\"content\">";
    //         echo "<a style=\"float:right;\" href=\"controller.php?cmd=checkoutView\">Go to checkout</a> &nbsp";
    //         echo "<div id=\"search\">
    //                 <form action=\"controller.php\" method=\"post\">
    //                 <input type=\"hidden\" name=\"cmd\" value=\"search\">
                
    //                 <table>
    //                 <tr>
    //                     <td>
    //                     Route:
    //                     </td>
    //                     <td>
    //                     <select name=\"route\" >
                    
    //             ";
    //         // need to get the route decsriptions and ids out of the database
    //         $DBM = new DBManager();
    //         $data = $DBM->getAllRoutes();

    //         for ($i = 0; $i < sizeof($data); $i++) {

    //             /*        echo "<option value=\"{$data[$i]['id']}\"> {$data[$i]['source']} - {$data[$i]['dest']}
    //             </option> "; */
    //             if ($data[$i]['id'] == $_SESSION['route'])
    //                 echo "<option value=\"{$data[$i]['id']}\" selected> {$data[$i]['source']} - {$data[$i]['dest']}
    //                                     </option> ";
    //             else
    //                 echo "<option value=\"{$data[$i]['id']}\"> {$data[$i]['source']} - {$data[$i]['dest']}
    //                                     </option> ";
    //         }

    //         echo "
    //                         </select>
    //                         </td>
    //                         </tr>
    //                         <tr>
    //                             <td>
    //                                 Number of tickets:   
    //                             </td>
    //                         <td>
    //                         <input type=\"text\" name=\"seatCount\" value=\"{$_SESSION['seatCount']}\" size=\"3\" >
    //                         </td
    //                     </tr>
    //                 <tr>
    //                     <td> 
    //                         Select a date:
    //                     </td>
    //                         <td>
                            
    //                         <input id=\"startdate\" type=\"date\" name=\"date\" value=\"{$_SESSION['date']}\" >
    //                         </td>
    //                 </tr>    
                    
    //                 <tr><td>
    //                         <input type=\"submit\" value=\"search\">
    //                 </tr>    
                    
                    
                    
    //             </table>
            
                
    //         </form>
    //         </div>
    //         ";
    //     }
    //     function top($hasCookie = false) {
    //         parent::top($hasCookie);
    //         $username = $_COOKIE['username'] ?? ($_SESSION['username'] ?? '');
    //         if ($username !== '') {
    //             echo '<p class="welcome-message">Welcome ' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . '</p>';
    //         }
    //     }
    //     public function menu($info = false) {
    //         $loggedIn = !empty($info) || isset($_COOKIE['username']);
    //         echo '<nav id="menu" class="old" aria-label="Main navigation">
    //             <a href="controller.php">Home</a>
    //             <a href="controller.php?cmd=search">Search flights</a>';
    //         if ($loggedIn) {
    //             echo '<a href="controller.php?cmd=checkoutView">Checkout</a><a href="controller.php?cmd=logout">Logout</a>';
    //         } else {
    //             echo '<a href="controller.php?cmd=login">Login</a><a href="controller.php?cmd=register">Register</a>';
    //         }
    //         echo '<a href="controller.php?cmd=login">Staff / admin</a></nav>';
    //     }

    //         function bottom() {
    //         parent::bottom();
    //     }

    // }

    /**
     * SearchView
     *
     * Displays the flight-search page for the Airline2014 application.
     * Extends OutsideView to reuse the public page layout.
     *
     * Provides a search form allowing users to:
     * - Select a flight route retrieved from the database.
     * - Specify the number of tickets required.
     * - Select a travel date.
     *
     * Retains previous search criteria using session variables.
     * Submits the search request to controller.php for processing.
     *
     * Also displays the page navigation and, when a username is
     * available, a welcome message.
     */
    class SearchView extends OutsideView {

        // Build the flight-search page using the shared public-page layout.
        public function __construct($info) {

            // Render the page header and navigation.
            $this->top($info);
            $this->menu($info);

            // The blogger list is not currently displayed on this page.
            // $this->bloggerList();

            // Render the flight-search form.
            $this->searchDiv();

            // Finish the page layout.
            $this->bottom();
        }

        // Display the form used to search for flights by route, seat count and date.
        function searchDiv() {

            echo "<div id=\"content\">";

            // Provide a direct link to the checkout page.
            echo "<a style=\"float:right;\" href=\"controller.php?cmd=checkoutView\">Go to checkout</a> &nbsp";

            // Submit the search criteria to controller.php using the POST method.
            echo "<div id=\"search\">
                    <form action=\"controller.php\" method=\"post\">
                    <input type=\"hidden\" name=\"cmd\" value=\"search\">

                    <table>
                    <tr>
                        <td>
                        Route:
                        </td>
                        <td>
                        <select name=\"route\" >

                ";

            // Retrieve the available routes, including their IDs and descriptions.
            $DBM = new DBManager();
            $data = $DBM->getAllRoutes();

            // Create one option in the route selector for each route returned by the database.
            for ($i = 0; $i < sizeof($data); $i++) {

                /*
                * Previous version of the route option output.
                * Retained as a comment for reference.
                *
                * echo "<option value=\"{$data[$i]['id']}\"> {$data[$i]['source']} - {$data[$i]['dest']}
                * </option> ";
                */

                // Select the route stored in the session so the user's previous choice is retained.
                if ($data[$i]['id'] == $_SESSION['route'])
                    echo "<option value=\"{$data[$i]['id']}\" selected> {$data[$i]['source']} - {$data[$i]['dest']}
                                        </option> ";
                else
                    // Display other routes without selecting them.
                    echo "<option value=\"{$data[$i]['id']}\"> {$data[$i]['source']} - {$data[$i]['dest']}
                                        </option> ";
            }

            echo "
                            </select>
                            </td>
                            </tr>
                            <tr>
                                <td>
                                    Number of tickets:
                                </td>
                            <td>
                            <input type=\"text\" name=\"seatCount\" value=\"{$_SESSION['seatCount']}\" size=\"3\" >
                            </td
                        </tr>
                    <tr>
                        <td>
                            Select a date:
                        </td>
                            <td>

                            <input id=\"startdate\" type=\"date\" name=\"date\" value=\"{$_SESSION['date']}\" >
                            </td>
                    </tr>

                    <tr><td>
                            <input type=\"submit\" value=\"search\">
                    </tr>



                </table>


            </form>
            </div>
            ";
        }

        // Render the inherited page header, then show a welcome message when a username is available.
        function top($hasCookie = false) {

            // Reuse the header implementation supplied by OutsideView.
            parent::top($hasCookie);

            // Prefer the username cookie; fall back to the session username.
            // Use an empty string if neither value is available.
            $username = $_COOKIE['username'] ?? ($_SESSION['username'] ?? '');

            // Escape the username before inserting it into HTML.
            if ($username !== '') {
                echo '<p class="welcome-message">Welcome ' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . '</p>';
            }
        }

        // Render the main navigation, choosing links according to the supplied login information.
        public function menu($info = false) {

            // This checks for supplied user information or a username cookie.
            // It controls which links are displayed; it is not, by itself, an authorization check.
            $loggedIn = !empty($info) || isset($_COOKIE['username']);

            echo '<nav id="menu" class="old" aria-label="Main navigation">
                <a href="controller.php">Home</a>
                <a href="controller.php?cmd=search">Search flights</a>';

            // Logged-in users are offered checkout and logout links.
            if ($loggedIn) {
                echo '<a href="controller.php?cmd=checkoutView">Checkout</a><a href="controller.php?cmd=logout">Logout</a>';
            } else {
                // Guests can log in or register.
                echo '<a href="controller.php?cmd=login">Login</a><a href="controller.php?cmd=register">Register</a>';
            }

            // Staff/admin users are directed to the login entry point.
            echo '<a href="controller.php?cmd=login">Staff / admin</a></nav>';
        }

        // Delegate page-footer rendering to the parent class.
        function bottom() {
            parent::bottom();
        }

    }



    /**
     * ResultsView
     *
     * Displays flight-search results for a selected route, ticket quantity
     * and travel date.
     *
     * Extends SearchView to reuse the existing public-page layout,
     * navigation and flight-search form.
     *
     * Checks a five-day date window around the requested travel date.
     * For each date, checks whether the route is operational and whether
     * enough seats are available.
     *
     * Displays the calculated total price and allows the user to select
     * an available flight date and add the selection to the cart.
     */
    class ResultsView extends SearchView {

        private $route;
        private $seatCount;
        private $date;

        public function __construct($route, $seatCount, $date, $info) {

            $this->route = $route;
            $this->seatCount = $seatCount;
            $this->date = $date;
            //       $this->info=$info;
            //  $info=1;
            $this->top($info);
            $this->menu($info);
            $this->searchDiv();
            //     $this->bloggerList();
            $this->results();
            $this->bottom();
        }
        public function menu($info = false) {
            $loggedIn = !empty($info) || isset($_COOKIE['username']);
            echo '<nav id="menu" class="old" aria-label="Main navigation">
                <a href="controller.php">Home</a>
                <a href="controller.php?cmd=search">Search flights</a>';
            if ($loggedIn) {
                echo '<a href="controller.php?cmd=checkoutView">Checkout</a><a href="controller.php?cmd=logout">Logout</a>';
            } else {
                echo '<a href="controller.php?cmd=login">Login</a><a href="controller.php?cmd=register">Register</a>';
            }
            echo '<a href="controller.php?cmd=login">Staff / admin</a></nav>';
        }

        
        public function results() {
            $DBM = new DBManager();
            $routeData = $DBM->getRoute($this->route);
            $startDate = new DateTime($routeData[0]['start']);
            $endDate = new DateTime($routeData[0]['end']);

            echo "start: " . $startDate->format('d-M-Y') . " end: " . $endDate->format('d-M-Y') . "<br><br>";
            $dates = array();
            $searchDate = new DateTime($this->date);

            echo "<div>";
            echo "<form action=\"controller.php\" method=\"post\">";
            echo "<input type=\"hidden\" name=\"cmd\" value=\"addToCart\">";
            echo "<input type=\"hidden\" name=\"route\" value=\"{$this->route}\">";
            echo "<input type=\"hidden\" name=\"seatCount\" value=\"{$this->seatCount}\">";
            echo "<input type=\"hidden\" name=\"date\" value=\"{$this->date}\">";
            $temp = 0;
            for ($i = -2; $i < 3; $i++) {
                if ($i == -2) {
                    $searchDate->sub(new DateInterval('P2D'));
                } else {
                    $searchDate->add(new DateInterval('P1D'));
                }

                $dates[$i] = clone $searchDate;
                if (($dates[$i]->getTimestamp() < $startDate->getTimestamp()) ||
                        ($dates[$i]->getTimestamp() > $endDate->getTimestamp())) {

                    echo $dates[$i]->format('d-M-Y') . " not operating <br>";
                } else {
                    // the route is operational on this date
                    // must go to database to see if there are enough seats for $seatCount
                    // checkSeat returns a boolean 


                    if ($DBM->checkSeats($dates[$i]->format('Y-m-d'), $this->route, $this->seatCount)) {

                        $totalPrice = $this->seatCount * $routeData[0]['price'];


                        echo $dates[$i]->format('d-M-Y') . "  $totalPrice";
                        if ($temp == 0) {
                            echo "<input type=\"radio\"  value=\"{$dates[$i]->format('Y-m-d')}\"name=\"flightDate\" checked>";
                            echo "<a href=\"controller.php?cmd=viewdate&amp;dateid={$dates[$i]->format('Y-m-d')}\"> \"{$dates[$i]->format('Y-m-d')}\"</a><br>";
                            $temp++;
                        } else {
                            echo "<input type=\"radio\" name=\"flightDate\" value=\"{$dates[$i]->format('Y-m-d')}\">";
                            echo "<a href=\"controller.php?cmd=viewdate&amp;dateid={$dates[$i]->format('Y-m-d')}\"> \"{$dates[$i]->format('Y-m-d')}\"</a><br>";
                        }
                    } else {
                        echo $dates[$i]->format('d-M-Y') . " Not enough seats <br>";
                    }
                }
            }

            if (!$temp)
                echo "<input type=\"submit\" value=\"Add to Cart\" disabled>";
            else
                echo "<input type=\"submit\" value=\"Add to Cart\">";
            echo "</form></div>";
        }

    }


    /**
     * Displays the passenger purchasing-details page, allowing guests to enter
     * their details or logged-in customers to retrieve their existing details.
     */
    class purchaseView extends SearchView {

        public function __construct($hasCookie) {



            $this->top($hasCookie);
            $this->menu($hasCookie);
            $this->getUserdetails($hasCookie);

            $this->bottom();
        }
        public function menu($info = false) {
            $loggedIn = !empty($info) || isset($_COOKIE['username']);
            echo '<nav id="menu" class="old" aria-label="Main navigation">
                <a href="controller.php">Home</a>
                <a href="controller.php?cmd=search">Search flights</a>';
            if ($loggedIn) {
                echo '<a href="controller.php?cmd=checkoutView">Checkout</a><a href="controller.php?cmd=logout">Logout</a>';
            } else {
                echo '<a href="controller.php?cmd=login">Login</a><a href="controller.php?cmd=register">Register</a>';
            }
            echo '<a href="controller.php?cmd=login">Staff / admin</a></nav>';
        }

        
        public function getUserdetails($hasCookie) {
            $hasCookie = false;
            //      echo $_COOKIE['username'];
            if (isset($_COOKIE['username'])) {
                // only create the cart when the session starts
                $hasCookie = true;
            }

            //      $info1=$info;
            echo "<div id=\"content\">";
            //      echo   "<a style=\"float:right;\" href=\"controller.php?cmd=checkoutView\">Go to checkout</a> &nbsp";
            //      echo "<a href=\"controller.php?cmd=login\">Login</a> &nbsp;";<a href
            if (!$hasCookie) {

                echo" <h1> Ticket/Purchasing Details </h1>
                        <form action=\"controller.php\" method=\"post\">
                    <input type=\"hidden\" name=\"cmd\" value=\"customerRequest\">
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

                $customerDetails = $DBM->handleretrieveCustomerdetails();
            }




            echo "</div>";
        }

    }

    /**
     * Displays the checkout page, including the shopping cart and controls
     * for submitting changes to the selected seats.
     */
    class CheckoutView extends SearchView {

    private $route;
    private $seatCount;
    private $date;

    public function __construct($info) {

        $this->top($info);
        $this->menu($info);
        //     $_SESSION['cart']->displayCart();
        $this->searchDiv();
        $this->bottom();
    }
    public function menu($info = false) {
        $loggedIn = !empty($info) || isset($_COOKIE['username']);
        echo '<nav id="menu" class="old" aria-label="Main navigation">
            <a href="controller.php">Home</a>
            <a href="controller.php?cmd=search">Search flights</a>';
        if ($loggedIn) {
            echo '<a href="controller.php?cmd=checkoutView">Checkout</a><a href="controller.php?cmd=logout">Logout</a>';
        } else {
            echo '<a href="controller.php?cmd=login">Login</a><a href="controller.php?cmd=register">Register</a>';
        }
        echo '<a href="controller.php?cmd=login">Staff / admin</a></nav>';
    }

    
    function searchDiv() {


        //      $info1=$info;
        echo "<div id=\"content\">
                     <form action=\"controller.php\" method=\"post\">
            
                     <input type=\"hidden\" name=\"cmd\" value=\"Update\">
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

//$_SESSION['cart']->displayCart();



    /**
     * Manages the database connection and provides methods for user authentication,
     * route management, customer bookings, seat availability and flight manifests.
     */
    class DBManager {

        private $db;

        public function __construct() {

            // $address = 'mysql:host=localhost:3306;dbname=airline2013';
            // $username = 'root';
            // $password = '';
//      $address = 'mysql:host=127.0.0.1;port=3307;dbname=airline2013';
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
     * Authenticates a user and returns their role or "invalid".
     */
        public function processLogin($username, $password) {

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
    
    /** Registers a new user, sets their session and cookie, and returns their details. */
        public function addUser(&$hasCookie) {

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
            $hasCookie = 1;           // shimmy
            $_SESSION['username'] = $_POST['username'];




            $date['email'] = $email;
            $data['CreditCardNo'] = $CreditCardNo;
            $data['username'] = $username;
            $data['id'] = $id;
            //      return $id;
            return $data;
        }

    /**
     * Updates the price of a route in the database.
     */
        public function updateRoute($value, $index) {

            $query = "update routes set price={$value} where id={$index}";
            $resultSet = $this->db->query($query);
        }

      /**
       * Checks seat availability; currently returns true unconditionally.
       */
        public function checkSeats() {
            return true;
        }

        /**
         * Retrieves all routes from the database. 
         * */
        public function getAllRoutes() {
            $query = "select * from routes";

            //db is a PDO object
            $resultSet = $this->db->query($query);

            return $resultSet->fetchAll();
        }

        /*
        * Retrieves a specific route from the database by its ID.
         */
        function getRoute($id) {
            $query = "select * from routes where id=$id";

            //db is a PDO object
            $resultSet = $this->db->query($query);

            return $resultSet->fetchAll();
        }

        /*
        * Inserts the two predefined sample routes into the database.
         */
        public function loadRoutes() {

            $query = "INSERT INTO routes 
                                    (source, dest, start, end, price) 
                                    VALUE ('Cork','London', '2014-01-01','2014-01-31',100)";

            $count = $this->db->exec($query);





            $query = "INSERT INTO routes 
                                    (source, dest, start, end, price) 
                                    VALUE ('Cork','Berlin', '2014-02-01','2014-02-28',100)";

            $count = $this->db->exec($query);
        }

        /*
        * Displays all routes with controls for deletion and links to edit them.
        */
        public function displayRoutes() {

            $query = "select DISTINCT * from routes";
            $resultSet = $this->db->query($query);
            echo "Delete routes \t\t (click to edit) <br>";
            echo " Y\tN <br>";

            for ($i = 0; $i < $resultSet->rowCount(); $i++) {
                $route = $resultSet->fetch();
                //     echo "{$user['username']} {$user['password']} <br>";  
                // echo "<input type=checkbox name={$route['source']} value={$route['source']}> {$route['source']}{$route['dest']}{$route['start']} - {$route['end']}  {$route['price']} <br>";
                /* echo " <input type=\"text\" name=\"firstname\" value={$route['source']}> 
                <input type=\"text\" name=\"firstname\" value={$route['dest']}>
                <input type=\"text\" name=\"firstname\" value={$route['start']}>
                <input type=\"text\" name=\"firstname\" value={$route['end']}>
                <input type=\"text\" name=\"firstname\" value={$route['price']}>
                <br>"; */
                $dummy = "route #" . $route['id'] . "  " . $route['source'] . "  " . $route['dest'] . "  " . $route['start'] . "  " . $route['end'] . "  " . $route['price'] . " <br>";
                $dummy1 = "r" . $i;
                echo "<input type=\"radio\" name=\"$dummy1\" value=\"Y\" >";
                echo "<input type=\"radio\"name=\"$dummy1\" value=\"N\" checked>";
                echo "<a href=\"controller.php?cmd=editroute&amp;routeid={$route['id']}\"> $dummy       </a><br>";
                //  echo $dummy."<br> ";    
            }
            return $resultSet;
        }

        /*
        * Displays route details in text areas. 
        */
        public function displayStaticRoutes() {

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

        /*
        * Displays the distinct route and date combinations with passenger bookings. 
        */
        public function displayActiveRoutes($info) {

            $query = "select DISTINCT route,date from passangers order by date";
            $resultSet = $this->db->query($query);

            for ($i = 0; $i < $resultSet->rowCount(); $i++) {
                $passangers = $resultSet->fetch();
                $dummy = "{$passangers['route']} {$passangers['date']}";
                echo "<a href=\"controller.php?cmd=openflight&amp;routeid={$passangers['route']}&amp;dateid={$passangers['date']}\"> $dummy</a><br>";

                /*   echo " <textarea name=\"textarea\" ROWS=6 COLS=50>";                 
                echo "Source:      {$route['source']} \nDestination: {$route['dest']}&nbsp;\nStart:       {$route['start']} &nbsp\nEnd:         {$route['end']}&nbsp;\nPrice:       {$route['price']}";
                echo " </textarea><br><br>" ;
                * var=value&var2=value2"
                */
            }
        }

        /*
        * /** Displays the details and price of the specified route for editing.
        */
        public function displayEditRoutes($info) {

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

        /*
        * Deletes routes selected through the submitted form. 
        */
        function deleteRoutes() {


            $query = "select * from routes";
            $resultSet = $this->db->query($query);

            for ($i = 0; $i < $resultSet->rowCount(); $i++) {
                $route = $resultSet->fetch();
                if ($_POST['r' . $i] == "Y") {
                    $dummy1 = $route['id'];
                    $query1 = "delete routes from routes where id = $dummy1";
                    $resultSet1 = $this->db->query($query1);
                } else {
                    //                      echo " Not Removing record index".$i." value".$_POST['b'.$i]."from database <br>";  
                }
            }
        }

        /*
        * Adds a new route with its source, destination, operating dates and price.
        */
        public function addRoute($source, $destination, $fromDate, $toDate, $price) {
            //  echo "/n".$fromDate;
            $from1 = $fromDate->format('Y-M-D');

            $to1 = $toDate->format('d-M-Y');
            $date = DateTime::createFromFormat('d/m/Y', "24/04/2012");
            echo $date->format('Y-m-d');
            $date1 = $fromDate->format('Y-m-d');
            $date2 = $toDate->format('Y-m-d');



            //  echo $price;

            /*  $query = "INSERT INTO routes 
            (source, dest, start, end, price)
            VALUE ($source,$destination,$from1,$to1,$price)"; */
            $query = "INSERT INTO routes 
                                (source, dest, start, end, price) 
                                    VALUE ('$source','$destination','$date1','$date2',$price)";

            $count = $this->db->exec($query);
        }

        /*
        * Returns the number of routes in the database. 
        */
        public function checkNmrroutes() {

            $query = "select * from routes";
            $resultSet = $this->db->query($query);
            return $resultSet->rowCount();
        }

        /*
        * Retrieves a customer's saved details and displays them in the purchasing form.
        */
        public function handleretrieveCustomerdetails() {

            $query = "select * from user";
            $resultSet = $this->db->query($query);

            for ($i = 0; $i < $resultSet->rowCount(); $i++) {


                $customer = $resultSet->fetch();

                if ($_COOKIE['username'] == $customer['username']) {
                    //        echo $customer['username'];  
                    echo" <h1> Ticket/Purchasing Details </h1>
                            <form action=\"controller.php\" method=\"post\">
                            <input type=\"hidden\" name=\"cmd\" value=\"customerRequest\">
                            <table>
                            <tr>
                    '      <td>Name as per records:</td>
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

        /*
        * Records a passenger booking, including route, date, seat and customer details.
        */
        public function processTransaction($username, $email, $CreditCardNo, $PassportNo, $route, $date, $seatNo) {



            $hasCookie = 0;
            $registerName = $_SESSION['username'];
            //      echo$registerName=$_SESSION['username'];

            if (isset($_COOKIE['username'])) {
                // only create the cart when the session starts
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
            /* */
            /*  */
        }

        /*
        * Displays passenger and seat-occupancy information for a specified date and the session route. 
        */
        public function processSeating($date) {
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
                //  echo "<br>".$result['date'];
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
            // print_r($seats);
        }

        /*
        * Marks occupied seats and calculates the number of free seats for a specified date and route. 
        */
        public function processSeating1($date, $nmrSeats, &$seats, &$freeSeats) {

            $Total = 0;
            $query1 = "select * from passangers ";
            $resultSet = $this->db->query($query1);
            $Size = $resultSet->rowCount();

            for ($i = 0; $i < $Size; $i++) {
                $result = $resultSet->fetch();
                //  echo "<br>".$result['date'];
                if (($result['date'] == $date) && ($result['route'] == $_SESSION['route'])) {

                    $dummy = $result['seatNo'];
                    $seats[$dummy] = "Y";
                    $Total++;
                }
            }

            /*    print_r($seats);  */
            $freeSeats = 20 - $Total;
        }

        /*
        * Displays flight details, passenger information, ticket price and calculated revenue.
         */
        public function processdisplayFlightdetails($date, $route) {
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
                ;
            }
        }

    }
?>

