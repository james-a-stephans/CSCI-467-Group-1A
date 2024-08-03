<?php
    //Initialize the webpage with the appropriate statements to print the header and import relevant functions.
    session_start();
    require './src/functions.php';
    printHeader();

    //Declares a flag that will be set if a quantity has to be adjusted due to insufficient stock.
    $qtyAdjust = false;

    //Set this page as the return page from the login page.
    $_SESSION['ReturnPage'] = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] .
    '?' . $_SERVER['QUERY_STRING'];

    //Display the current cart.
    echo '<div class="cart-box">';

    //If the user changed the quantity of an item in their cart, make the appropriate adjustment to the session variable.
    if(isset($_POST['productChange'])) {
        $_SESSION['cart'][$_POST['productChange']] = $_POST['newQty'];
    }

    if(isset($_SESSION['cart'])) {
        $items = array_keys($_SESSION['cart']);
        echo '<table class="cart">';

        //Initialize total weight and total price.
        $_SESSION['totalWt'] = 0;
        $_SESSION['totalPrice'] = 0;

        $col = 0;

        //Display items in the current cart.
        for($i = 0; $i < count($items); $i++) {

            //If an item in the cart has a quantity of 0, remove it from the cart.
            if($_SESSION['cart'][$items[$i]] == 0) {
                unset($_SESSION['cart'][$items[$i]]);

                continue;
            }
            
            //If the last row ended, start a new row.
            if($col % 3 == 0) {
                echo '<tr class="cart-part">';
            }

            //Fetch the details of the current part from both databases.
            $result = $pdo->query("SELECT * FROM `parts` WHERE `number` = " . $items[$i] . ";");
            $row = $result->fetch(PDO::FETCH_NUM);
            $local_result = $local_pdo->query("SELECT quantity FROM `products` WHERE `partnumber` = " . $items[$i] . ";");
            $local_row = $local_result->fetch(PDO::FETCH_NUM);

            $displayQty = $_SESSION['cart'][$items[$i]];

            //If the quantity in the cart exceeds the quantity on hand, adjust it to be the quantity on hand.
            if($_SESSION['cart'][$items[$i]] > $local_row[0]) {
                $_SESSION['cart'][$items[$i]] = $local_row[0];
                $displayQty = $_SESSION['cart'][$items[$i]] . "*";
                $qtyAdjust = true;
            }

            //Compute the weight for the quantity of the current item, then add it to the total.
            $itemWt = $row[3] * $_SESSION['cart'][$items[$i]];
            $_SESSION['totalWt'] += $itemWt;

            //Compute the price for the quantity of the current item, then add it to the total.
            $itemPrice = $row[2] * $_SESSION['cart'][$items[$i]];
            $_SESSION['totalPrice'] += $itemPrice;

            //Format the price and weight to display in their correct formats.
            $formatItemPrice = number_format($itemPrice, 2);
            $formatItemWeight = number_format($itemWt, 2);

            //Print the details for each part.
            echo '<td class="cart-part"><img src="' . $row[4] . "\" />\t";
            echo "\nProduct: " . ucfirst($row[1])
            . "\nQuantity in cart: " . $displayQty
            . "\nItem weight: " . $itemWt . ' lbs.'
            . "\nItem Price: $" . $formatItemPrice
            . "\n" . '<form method="POST" action="./checkout.php">'
            .'<input type="number" id="newQty" name="newQty" value="' . $_SESSION['cart'][$items[$i]] . '" min="0" max="'
            . $local_row[0]
            . '"/><input type="submit" value="Change Quantity" name="qtyChange" />'
            . '<input type="hidden" value="' . $row[0] . '" name="productChange" />';

            echo '</form></td>' . "\n";

            //If there are now 3 products on a row, end this row.
            if(($col + 1) % 3 == 0)
            {
                echo '</tr>';
            }
            $col++;
        }
        
        echo '</table>';

        //Computes the shipping cost for the order, then adds it to the total price.
        $queryStatement = "SELECT MAX(price) FROM weightbrackets WHERE price <= '" . $_SESSION['totalWt'] . "';";
        $shippingRow = $local_pdo->query($queryStatement);
        $shippingPrice = $shippingRow->fetch(PDO::FETCH_NUM);
        $_SESSION['totalPrice'] += $shippingPrice[0];

        //Format the total price and weight to display in their correct formats.
        $_SESSION['totalPrice'] = round($_SESSION['totalPrice'], 2);
        $formatTotalWt = number_format($_SESSION['totalWt'], 2);
        $formatTotalPrice = number_format($_SESSION['totalPrice'], 2);

        //Display the final weight and price.
        echo '<div class="order-footer">'
        . '<form method="POST" action="./checkout.php">' . "\nTotal Weight: " . $formatTotalWt . ' lbs.';
        echo "\nTotal Price: $" . $formatTotalPrice;
        echo "\n" . '<input type="submit" value="Place Order" name="orderPlaced" />';
        echo '</form></div></div>';

        //If the user has no items in their cart, remove the cart session variable and reload the page.
        if(count(array_keys($_SESSION['cart'])) == 0) {
            unset($_SESSION['cart']);
            resetUserPage();
        }
    }

    else {
        echo '<h3>Your cart is empty.</h3>';
    }

    //If the user is ready to place an order, prompt them for their payment information and email address.
    if(isset($_POST['orderPlaced']))
    {
        echo '<div class="pay-box"><form method="POST" action="./checkout.php" class="pay-form">
        <label for="custName">Name as it appears on your card: </label>'
        . '<input type="text" id="custName" name="custName" autocomplete="cc-name" required />' . "\n\n"
        . '<label for="custCard">        Credit Card Number (13-19 digits): </label>'
        . '<input type="tel" id="custCard" name="custCard" pattern="[0-9\s]{13,19}" autocomplete="cc-number" placeholder="xxxx xxxx xxxx xxxx" maxlength="19" required/>' . "\n\n"
        . '<label for="expDate">        Expiration Date (MM/YYYY): </label>'
        . '<input type="text" id="expDate" name="expDate" pattern="[0-9]{2,2}/[0-9]{4,4}" autocomplete="cc-exp" placeholder="xx/xxxx" maxlength="7" required />' . "\n\n"
        . '<label for="emailAddr">        Email Address: </label>'
        . '<input type="email" id="emailAddr" name="emailAddr" autocomplete="email" pattern="[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$" maxlength="50" required />' . "\n\n"
        . '<input type="submit" value="Finalize Order" name="finalOrder" />';
        echo '</form></div>';
    }

    //If the user has finished their order, process their credit card and update the database.
    if(isset($_POST['finalOrder'])) {
        //Fetch the latest order from the database.
        $orderRow = $local_pdo->query("SELECT MAX(orderno) FROM orders;");
        $currentOrder = $orderRow->fetch(PDO::FETCH_NUM)[0];

        //If there are no orders in the database, the current order will be set to order 1.
        if(!isset($currentOrder)) {
            $currentOrder = 1;
        }

        else {
            $currentOrder++;
        }

        //Begin a transaction with the local database.
        $begin = $local_pdo->beginTransaction();
        $transaction = $begin;

        //Do not continue placing the order if a transaction could not be started.
        if($begin == false)
        {
            transactionFailure();
        }

        else {
            //Prepare the statements to update the database.
            $addOrder = $local_pdo->prepare("INSERT INTO orders(email, partnumber, quantity, orderno, status) VALUES (?, ?, ?, ?, 'N')");
            $updateQty = $local_pdo->prepare("UPDATE products SET quantity=? WHERE partnumber=?;");

            //Iterate through every item in the cart.
            $orderItems = array_keys($_SESSION['cart']);

            for($i = 0; $i < count($orderItems); $i++) {
                //Find the current quantity of the item being processed.
                $local_result = $local_pdo->query("SELECT quantity FROM `products` WHERE `partnumber` = " . $orderItems[$i] . ";");
                $local_row = $local_result->fetch(PDO::FETCH_NUM);

                //Update the local database as appropriate.
                $addRes = $addOrder->execute(array($_POST['emailAddr'], $orderItems[$i], $_SESSION['cart'][$orderItems[$i]], $currentOrder));
                $updateRes = $updateQty->execute(array($local_row[0] - $_SESSION['cart'][$orderItems[$i]], $orderItems[$i]));

                //If an update to the database fails, stop the loop immediately and print an error message.
                if($addRes == false || $updateRes == false) {
                    $transaction = false;
                    transactionFailure();
                }
            }

            //If the transaction did not succeed, call rollback and do not progress.
            if($transaction == false) {
                $local_pdo->rollBack();
            }

            //If the transaction did succeed, process payment.
            else {
                $url = 'http://blitz.cs.niu.edu/CreditCard/';
                $transactionNum = rand(100000000,999999999);
                $data = array(
                    'vendor' => 'VE718-24',
                    'trans' => '718-' . $transactionNum . '-024',
                    'cc' => $_POST['custCard'],
                    'name' => $_POST['custName'], 
                    'exp' => $_POST['expDate'], 
                    'amount' => $_SESSION['totalPrice']);

                $options = array(
                    'http' => array(
                        'header' => array('Content-type: application/json', 'Accept: application/json'),
                        'method' => 'POST',
                        'content'=> json_encode($data)
                    )
                );

                $context  = stream_context_create($options);
                $result = file_get_contents($url, false, $context);
                $resultArray = json_decode($result, true);

                //If there were any errors with payment processing, rollback the transaction and print an error.
                if(isset($resultArray['errors']))
                {
                    echo '<p class="error-message">Your order was not completed. Reason(s):';
                    foreach($resultArray['errors'] as $err)
                    {
                        echo "\n" . $err;
                    }
                    echo "\nWe apologize for any inconvenience.</p>";
                }

                //If there were no errors, commit the transaction, empty the users cart, and reload the page.
                else {
                    $local_pdo->commit();
                    unset($_SESSION['cart']);
                    unset($_SESSION['totalWt']);
                    unset($_SESSION['totalPrice']);
                    resetUserPage();
                }
            }
        }

        //}
    }

    //If the quantity of one or more items was adjusted, print a message informing the user.
    if($qtyAdjust == true) {
        echo '<p class="error-message">Due to insufficient stock, the quantity of ';
        echo 'any items marked with a "*" in your cart have been adjusted.
        We apologize for any inconvenience.</p>';
    }

    //Close the body and html tags after running the relevant functions.
    echo '
    </body>
</html>';
?>