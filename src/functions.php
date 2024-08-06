<?php
    $username = convert_uudecode("(>C\$Y-S@T-#@` `");
    $password = convert_uudecode("),3DY-DYO=C`U `");
    $secstr = convert_uudecode("(>C\$Y-S@T-#@` `");

    //Connect to the databases necessary for the website.
    try {// if something goes wrong, an exception is thrown
        $dsn = "mysql:host=blitz.cs.niu.edu;dbname=csci467";
        $pdo = new PDO($dsn, 'student', 'student');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    }
    catch(PDOexception $e)  { // handle that exception
        $pdo = false;
    }

    //Connect to the databases necessary for the website.
    try {// if something goes wrong, an exception is thrown
        $dsn = "mysql:host=courses;dbname=z1978448";
        $local_pdo = new PDO($dsn, "z1978448", "1996Nov05");
        $local_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    }
    catch(PDOexception $e)  { // handle that exception
        $local_pdo = false;
    }

    /**
     * Prints a login box for the user to input their username and password.
     */
    function showLoginBox() {
        echo '<div class="loginbox">
            <p>
                <form method="POST" action="./login.php">
                    <label for="username" class="login-elt">
                        Username:
                    </label>
                    <input type="text" id="username" name="username" />
                    <label for="password" class="login-elt">
                        Password:
                    </label>
                    <input type="password" id="password" name="password" />
                    <div>
                        <input type="submit" value="Login" />
                    </div>
                    <input type="hidden" name="loginAttempt" value="true" />
                </form>
            </p>
        </div>';
    }

    /**
     * Print the header and associated links for the website.
     */
    function printHeader() {
        echo '
<!DOCTYPE html>
<html>
    <head>
        <title>DuCSS Parts Store</title>
        <link rel="stylesheet" href="./assets/style.css" />
    </head>
    <body>
        <header>
            <h1>DuCSS Parts Store</h1>
            <h2 class="header-band">';
                if(isset($_SESSION['adminLogin'])) {
                    echo '<a href="./weight.php" title="Set Weight Price Brackets" class="header-links">&#x1F4E6;</a>' . "\n";
                    echo '<a href="./vieworder.php" title="View Orders" class="header-links">&#128269;</a>' . "\n";
                }
                if(isset($_SESSION['receivingLogin'])) {
                    echo '<a href="./process.php" title="Process Delivery" class="header-links">&#x1F69A;</a>' . "\n";
                }
                if(isset($_SESSION['warehouseLogin'])) {
                    echo '<a href="./fulfill.php" title="Fulfill Order" class="header-links">&#9757;</a>' . "\n";
                }
                echo '<a href="./index.php" title="Home Page" class="header-links">&#x1F3E0;</a>' . "\n";
                echo '<a href="./parts.php" title="Products" class="header-links">&#9991;</a>' . "\n";
                echo '<a href="./checkout.php" title="View Cart" class="header-links">&#x1F6D2;</a>' . "\n";

                if(!isset($_SESSION['username'])) {
                    echo '<a href="./login.php" title="Login" class="header-links">&#x1F511;</a>';
                }

                else {
                    echo '<a href="./login.php" title="Logout" class="header-links">&#x1F512;</a>';
                }
                echo '
            </h2>
        </header>';
    }

    /**
     * Validates the password provided by the user.
     * 
     * @param loginInfo The login information associated with the username provided
     * at the login screen.
     */
    function passwordValidation($loginInfo) {
        if(gettype($loginInfo) != "boolean" && password_verify($_POST['password'], $loginInfo[1])) {
            $_SESSION['username'] = $_POST["username"];

            //If the user is an administrator, mark that status in the session.
            if($loginInfo[2] == "administrator")
            {
                $_SESSION['adminLogin'] = true;
            }
            else if ($loginInfo[2] == "warehouse")
            {
                $_SESSION['warehouseLogin'] = true;
            }
            else if ($loginInfo[2] == "receiving")
            {
                $_SESSION['receivingLogin'] = true;
            }

            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Return the user to their previous page, or to the home page if they accessed their current
     * page directly.
     */
    function resetUserPage() {
        if (isset($_SESSION['ReturnPage'])) {
            header("Location: " . $_SESSION['ReturnPage']);
        }
    
        else {
            header("Location: ./index.php");
        }
    }

    /**
     * Display an error message if the user attempted to access a page they do not have the authorization
     * to view.
     */
    function denyAccess() {
        if($_SESSION['accessDenied']) {
            echo '<h3 class="access-denial">Access denied.</h3>';
            unset($_SESSION['accessDenied']);
        }
    }
    
    /**
     * Display the orders table for the administrator to view.
     * 
     * @param orders The results from a query to the orders table.
     * @param pdo The pdo object used for queries.
     * @param rows The results from a query to the orderInfo table.
     */
    function showOrderTable($orders, $pdo, $rows) {
        if(count($orders) == 0){
            echo '<p> No orders found. </p>';
        }
        else{
            //Iterate through each order.
            foreach($orders as $order){
                echo '<div class="orderbox">';
                echo '<form method="POST" action="">';

                //Print the table headers for the order table header.
                echo '<p><table class="all-orders">
                    <tr>
                        <th class="orderhead">Order Number</th>
                        <th class="orderhead">Customer Email</th>
                        <th class="orderhead">Customer Name</th>
                        <th class="orderhead">Customer Address 1</th>
                        <th class="orderhead">Customer Address 2</th>
                        <th class="orderhead">Shipping and Handling</th>
                        <th class="orderhead">Total Price</th>
                        <th class="orderhead">Status</th>
                    </tr>';

                //Print the information for the current order.
                echo '<tr>
                    <td class ="order">' . $order['orderno'] . '</td>
                    <td class ="order">' . $order['email'] . '</td>
                    <td class ="order">' . $order['customer_name'] . '</td>
                    <td class ="order">' . $order['address_street'] . '</td>
                    <td class ="order">' . $order['address_city'] . ', ' . $order['address_state'] . ', ' . $order['postcode'] . '</td>
                    <td class ="order">$' . number_format($order['shipping_cost'], 2) . '</td>
                    <td class ="order">$' . $order['total_cost'] . '</td>
                    <td class="order">';
                if ($order['status'] == 'Y'){
                    echo 'Shipped';
                }
                else {
                    echo 'Pending';
                }
                echo '</td></tr>';
                echo '</table></p>';

                //Print the table headers for the part table.
                echo '<p><table class="all-orders">
                        <tr>
                            <th class="orderhead">Part Number</th>
                            <th class="orderhead">Description</th>
                            <th class="orderhead">Price</th>
                            <th class="orderhead">Quantity</th>
                            <th class="orderhead">Weight per Product</th>
                            <th class="orderhead">Total Item Weight</th>
                            <th class="orderhead">Total Item Price </th>
                        </tr>';

                $weights[$order['orderno']] = 0;

                //Get the part information for the order.
                foreach($rows as $row) {
                    if($row['orderno'] == $order['orderno']) {
                        $partnumber = $row['partnumber'];
                        $stmt = $pdo->prepare("SELECT description, price, weight FROM parts WHERE number = :partnumber");
                        $stmt->execute(['partnumber' => $partnumber]);
                        $part = $stmt->fetch();
                        $description = $part['description'];
                        $price = $part['price'];
                        $totalprice = $price * $row['quantity'];

                        echo '<tr>
                        <td class ="order">' . $row['partnumber'] . '</td>
                        <td class ="order">' . $description . '</td>
                        <td class ="order">'.'$'. $price . '</td>
                        <td class ="order">' . $row['quantity'] . '</td>
                        <td class ="order">' . number_format($part['weight'], 2) . 'lbs</td>
                        <td class ="order">' . number_format($part['weight'] * $row['quantity'], 2) . 'lbs</td>
                        <td class ="order">'.'$'. $totalprice . '</td>';

                        $weights[$order['orderno']] += $part['weight'] * $row['quantity'];
                    }
                }

                echo '</table></p>';
                echo '</div>';
            }
        }
    }

    /**
     * Display the orders table for the Warehouse Worker to view.
     * 
     * @param orders The results from a query to the orders table.
     * @param pdo The pdo object used for queries.
     * @param rows The results from a query to the orderInfo table.
     */
    function showOrderTableFulfill($orders, $pdo, $rows) {
        if(count($orders) == 0){
            echo '<p> No orders found. </p>';
        }
        else{
            //Iterate through each order.
            foreach($orders as $order){
                echo '<div class="orderbox">';
                echo '<form method="POST" action="">';

                //Print the table headers for the order table header.
                echo '<p><table class="all-orders">
                    <tr>
                        <th class="orderhead">Order Number</th>
                        <th class="orderhead">Customer Email</th>
                        <th class="orderhead">Customer Name</th>
                        <th class="orderhead">Customer Address 1</th>
                        <th class="orderhead">Customer Address 2</th>
                        <th class="orderhead">Shipping and Handling</th>
                        <th class="orderhead">Total Price</th>
                        <th class="orderhead">Status</th>
                    </tr>';

                //Print the information for the current order.
                echo '<tr>
                    <td class ="order">' . $order['orderno'] . '</td>
                    <td class ="order">' . $order['email'] . '</td>
                    <td class ="order">' . $order['customer_name'] . '</td>
                    <td class ="order">' . $order['address_street'] . '</td>
                    <td class ="order">' . $order['address_city'] . ', ' . $order['address_state'] . ', ' . $order['postcode'] . '</td>
                    <td class ="order">$' . number_format($order['shipping_cost'], 2) . '</td>
                    <td class ="order">$' . $order['total_cost'] . '</td>
                    <td class="order">';
                if ($order['status'] == 'N') {
                    echo '<select name="shipment_status[' . $order['orderno'] . ']">
                            <option value="not shipped" selected>Not shipped</option>
                            <option value="shipped">Shipped</option>
                          </select>';
                }
                else {
                    echo 'Shipped';
                }
                echo '</td></tr>';
                echo '</table></p>';

                //Print the table headers for the part table.
                echo '<p><table class="all-orders">
                        <tr>
                            <th class="orderhead">Part Number</th>
                            <th class="orderhead">Description</th>
                            <th class="orderhead">Price</th>
                            <th class="orderhead">Quantity</th>
                            <th class="orderhead">Weight per Product</th>
                            <th class="orderhead">Total Item Weight</th>
                            <th class="orderhead">Total Item Price </th>
                        </tr>';

                $weights[$order['orderno']] = 0;

                //Get the part information for the order.
                foreach($rows as $row) {
                    if($row['orderno'] == $order['orderno']) {
                        $partnumber = $row['partnumber'];
                        $stmt = $pdo->prepare("SELECT description, price, weight FROM parts WHERE number = :partnumber");
                        $stmt->execute(['partnumber' => $partnumber]);
                        $part = $stmt->fetch();
                        $description = $part['description'];
                        $price = $part['price'];
                        $totalprice = $price * $row['quantity'];

                        echo '<tr>
                        <td class ="order">' . $row['partnumber'] . '</td>
                        <td class ="order">' . $description . '</td>
                        <td class ="order">'.'$'. $price . '</td>
                        <td class ="order">' . $row['quantity'] . '</td>
                        <td class ="order">' . number_format($part['weight'], 2) . 'lbs</td>
                        <td class ="order">' . number_format($part['weight'] * $row['quantity'], 2) . 'lbs</td>
                        <td class ="order">'.'$'. $totalprice . '</td>';

                        $weights[$order['orderno']] += $part['weight'] * $row['quantity'];
                    }
                }

                echo '</table></p>';

                //Show the button to confirm an order shipment.
                echo '<button type="submit">Confirm</button>';
                echo '</form>';
                echo '<form action="./src/labels.php" target="_blank" method="post">';

                //Show the button to print a shipping label.
                echo '<button type="submit">Generate Shipping Label</button>'
                . '<input type="hidden" value="' . $weights[$order['orderno']] . '" name="orderWeight" />'
                . '<input type="hidden" value="' . $order['customer_name'] . '" name="orderName" />'
                . '<input type="hidden" value="' . $order['orderno'] . '" name="orderNo" />'
                . '<input type="hidden" value="' . $order['address_street'] . '" name="orderSt" />'
                . '<input type="hidden" value="' . $order['address_city'] . ' ' . $order['address_state'] . ' ' . $order['postcode'] . '" name="orderCity" />';
                echo '</form>';

                //Show the button to print a packing list.
                echo '<form action="./src/packing.php" target="_blank" method="post">';
                echo '<button type="submit">Generate Packing List</button>'
                . '<input type="hidden" value="' . $order['orderno'] . '" name="orderNo" />';
                echo '</form>';

                //Show the button to print an invoice.
                echo '<form action="./src/invoice.php" target="_blank" method="post">';
                echo '<button type="submit">Generate Invoice</button>'
                . '<input type="hidden" value="' . $weights[$order['orderno']] . '" name="orderWeight" />'
                . '<input type="hidden" value="' . $order['customer_name'] . '" name="orderName" />'
                . '<input type="hidden" value="' . $order['orderno'] . '" name="orderNo" />'
                . '<input type="hidden" value="' . $order['address_street'] . '" name="orderSt" />'
                . '<input type="hidden" value="' . $order['address_city'] . ' ' . $order['address_state'] . ' ' . $order['postcode'] . '" name="orderCity" />';
                echo '</form>';
                echo '</div>';
            }
        }
    }

      
    /** 
     * Display an error message if something goes wrong while placing an order.
     */
    function transactionFailure() {
        echo '<p class="error-message">Something has gone wrong on our end.
        We apologize for any inconvenience.</p>';
    }

    /**
     * Returns a dropdown list for all states and outlying territories in the US.
     * 
     * @param tagName The name for the select tag generated.
     */
    function getStates($tagName) {
        return '<select name=' . $tagName . '>
        <option value="AL">Alabama</option>
        <option value="AK">Alaska</option>
        <option value="AZ">Arizona</option>
        <option value="AR">Arkansas</option>
        <option value="CA">California</option>
        <option value="CO">Colorado</option>
        <option value="CT">Connecticut</option>
        <option value="DE">Delaware</option>
        <option value="DC">District Of Columbia</option>
        <option value="FL">Florida</option>
        <option value="GA">Georgia</option>
        <option value="HI">Hawaii</option>
        <option value="ID">Idaho</option>
        <option value="IL">Illinois</option>
        <option value="IN">Indiana</option>
        <option value="IA">Iowa</option>
        <option value="KS">Kansas</option>
        <option value="KY">Kentucky</option>
        <option value="LA">Louisiana</option>
        <option value="ME">Maine</option>
        <option value="MD">Maryland</option>
        <option value="MA">Massachusetts</option>
        <option value="MI">Michigan</option>
        <option value="MN">Minnesota</option>
        <option value="MS">Mississippi</option>
        <option value="MO">Missouri</option>
        <option value="MT">Montana</option>
        <option value="NE">Nebraska</option>
        <option value="NV">Nevada</option>
        <option value="NH">New Hampshire</option>
        <option value="NJ">New Jersey</option>
        <option value="NM">New Mexico</option>
        <option value="NY">New York</option>
        <option value="NC">North Carolina</option>
        <option value="ND">North Dakota</option>
        <option value="OH">Ohio</option>
        <option value="OK">Oklahoma</option>
        <option value="OR">Oregon</option>
        <option value="PA">Pennsylvania</option>
        <option value="RI">Rhode Island</option>
        <option value="SC">South Carolina</option>
        <option value="SD">South Dakota</option>
        <option value="TN">Tennessee</option>
        <option value="TX">Texas</option>
        <option value="UT">Utah</option>
        <option value="VT">Vermont</option>
        <option value="VA">Virginia</option>
        <option value="WA">Washington</option>
        <option value="WV">West Virginia</option>
        <option value="WI">Wisconsin</option>
        <option value="WY">Wyoming</option>
        <option value="AS">American Samoa</option>
        <option value="GU">Guam</option>
        <option value="MP">Northern Mariana Islands</option>
        <option value="PR">Puerto Rico</option>
        <option value="UM">United States Minor Outlying Islands</option>
        <option value="VI">Virgin Islands</option>
        <option value="AA">Armed Forces Americas</option>
        <option value="AP">Armed Forces Pacific</option>
        <option value="AE">Armed Forces Others</option>
        </select>';
    }
?>
