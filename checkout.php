<?php
    //Initialize the webpage with the appropriate statements to print the header and import relevant functions.
    session_start();
    require './src/functions.php';
    printHeader();

    //TODO: Finalize logic for placing an order, to include credit card authorization, deducting item quantity from
    //the new database, etc.
    if(isset($_POST['orderPlaced']))
    {
        unset($_SESSION['cart']);
    }

    //Set this page as the return page from the login page.
    $_SESSION['ReturnPage'] = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] .
    '?' . $_SERVER['QUERY_STRING'];

    //Display the current cart.
    echo '<div class="cart-box">';

    if(isset($_SESSION['cart'])) {
        echo '<table class="cart">';
        $items = array_keys($_SESSION['cart']);

        //Initialize total weight and total price.
        $totalWt = 0;
        $totalPrice = 0;

        //Display items in the current cart.
        for($i = 0; $i < count($items); $i++) {

            //If the last row ended, start a new row.
            if($i % 3 == 0) {
                echo '<tr class="cart-part">';
            }

            $result = $pdo->query("SELECT * FROM `parts` WHERE `number` = " . $items[$i] . ";");
            $row = $result->fetch(PDO::FETCH_NUM);

            //Compute the weight for the quantity of the current item, then add it to the total.
            $itemWt = $row[3] * $_SESSION['cart'][$items[$i]];
            $totalWt += $itemWt;

            //Compute the price for the quantity of the current item, then add it to the total.
            $itemPrice = $row[2] * $_SESSION['cart'][$items[$i]];
            $totalPrice += $itemPrice;

            echo '<td class="cart-part"><img src="' . $row[4] . "\" />\t";
            echo "\nProduct: " . ucfirst($row[1])
            . "\nQuantity in cart: " . $_SESSION['cart'][$items[$i]]
            . "\nCombined item weight: " . $itemWt . ' lbs.</td>' . "\n";

            //If there are now 3 products on a row, end this row.
            if(($i + 1) % 3 == 0)
            {
                echo '</tr>';
            }
        }
        
        echo '</table>';

        //TODO: Add logic for computing shipping cost.
        echo '<div class="order-footer"><form method="POST" action="./checkout.php">' . "\nTotal Weight: " . $totalWt . ' lbs.';
        echo "\nTotal Price: $" . $totalPrice;
        echo '<input type="hidden" name="totPrice" value="' . $totalPrice . '"/>'
        . '<input type="hidden" name="totWt" value="' . $totalWt . '"/>'
        . "\n" . '<input type="submit" value="Place Order" name="orderPlaced" />';
        echo '</form></div>';
    }

    else {
        echo '<h3>Your cart is empty.</h3>';
    }

    //Close the body and html tags after running the relevant functions.
    echo '
    </body>
</html>';
?>