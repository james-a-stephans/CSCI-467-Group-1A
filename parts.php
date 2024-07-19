<?php
    //Initialize the webpage with the appropriate statements to print the header and import relevant functions.
    session_start();
    require './src/functions.php';
    printHeader();

    //Fetch the parts from the database.
    $result = $pdo->query("SELECT * FROM `parts`;");

    //Display the parts from the database in a table.
    $colNum = 0;
    echo
  "   <table class=\"part-info\">\n";
    while($row = $result->fetch(PDO::FETCH_NUM)) {
        //If this is the first column, print a new tr tag.
        if($colNum == 0) {
            echo "\n<tr class=\"part\">";
        }

        //Find the quantity remaining of an item, as well as the quantity that a customer can add to their current cart.
        $productQuantity = $local_pdo->query("SELECT quantity FROM `products` WHERE partnumber='" . $row[0] . "';");
        $qty = $productQuantity->fetch(PDO::FETCH_NUM)[0];
        $maxQty = $qty - $_SESSION['cart'][$row[0]];

        //Format the price and weight to display in their correct formats.
        $itemPrice = number_format($row[2], 2);
        $itemWeight = number_format($row[3], 2);

        //Print the data for the current database record.
        echo "\n<td class=\"part\"><img class=\"product-pic\" src=\"" . $row[4] . "\" /><div class=\"product-info\">Product: "
        . ucfirst($row[1]) ."\nPrice: $" . $itemPrice . "\nWeight: " . $itemWeight . " lbs.\nQuantity in store: ". $qty
        . "</div>" . '<form method="POST" action="./parts.php"><input type="hidden" name="partNum" value="' . $row[0] . '"/>'
        . '<input type="number" id="qtyOrdered" name="qtyOrdered" value="0" min="0" max="' . $maxQty
        . '"/><input type="submit" value="Add to Cart" name="itemAdd" />'
        . "</form></td>";

        $colNum++;

        //If there are four columns on the current row, start a new row.
        if($colNum == 4) {
            echo "\n</tr>";
            $colNum = 0;
        }
    }

    //Set this page as the return page from the login page.
    $_SESSION['ReturnPage'] = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] .
    '?' . $_SERVER['QUERY_STRING'];

    //If the user added something to their cart, update the session variable appropriately.
    if(isset($_POST['itemAdd']))
    {
        $_SESSION['cart'][$_POST['partNum']] += $_POST['qtyOrdered'];
    }

    //Close the body and html tags after running the relevant functions.
    echo '
    </body>
</html>';
?>