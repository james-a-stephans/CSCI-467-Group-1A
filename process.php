<?php
    //Initialize the webpage with the appropriate statements to print the header and import relevant functions.
    session_start();
    require './src/functions.php';
    printHeader();

    //Set this page as the return page from the login page.
    $_SESSION['ReturnPage'] = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] .
    '?' . $_SERVER['QUERY_STRING'];

    //If the user is not a desk employee, redirect them to the home page with an error message.
    if(!isset($_SESSION['receivingLogin'])) {
        $_SESSION['accessDenied'] = true;
        header("Location: ./index.php");
    }

    //show the process delivery interface
   echo '<div class="weightbox">
        <div>
            <p>Process Delivery
            <form method="POST" action="./process.php" class="weight-elt">
                <input type="radio" name="searchtype" value="number" checked /> by Product Number<br/>
                <input type="radio" name="searchtype" value="description" />by Product Name<br/>
                <input type="text" name="search"/><br/>
                <input type="submit" name="submit"/></p>
            </form>
        </div>';
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])){
        //check if the form was submitted and search for the product
        $searchtype = $_POST['searchtype'];
        $search = $_POST['search'];
        if($searchtype == 'number' && !is_numeric($search)){
            echo '<p> Please enter a valid product number</p>';
        }else{
            if($searchtype == 'number') {
                $stmt = $pdo->prepare("SELECT * FROM parts WHERE $searchtype = :search");
                $stmt->execute(['search' => $search]);
            }
            else {
                $stmt = $pdo->prepare("SELECT * FROM parts WHERE $searchtype LIKE :search");
                $stmt->execute(['search' => '%' . $search . '%']);
            }
            $searchMatched = false;

            while($product = $stmt->fetch()){
                $stmt2 = $local_pdo->prepare("SELECT * FROM products WHERE partnumber = :partnumber");
                $stmt2->execute(['partnumber' => $product['number']]);
                $inventory = $stmt2->fetch();
                echo '<div class="part">
                    Part Number: ' . $product['number'] . '<br/> 
                    Description: ' . $product['description']
                    .'<br/> Quantity: ' . $inventory['quantity'] . 
                    '<form method="POST" action=./process.php>
                    <input type="hidden" name="partnumber" value="' . $product['number'] . '"/>
                    <br/>Add products to inventory: <input type="text" name="quantity"><br/>
                    <input type="submit" name="submitquantity">
                </form></div>';
                $searchMatched = true;
            }

            if(!$searchMatched) {
                echo '<p>Product not found</p>';
            }
        }
    }
    //check if the form to add quantity to inventory was submitted and update the inventory
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submitquantity'])){
        $quantity = $_POST['quantity'];
        $partnumber = $_POST['partnumber'];
        if(is_numeric($quantity)){
            $addQuantity = $local_pdo->prepare("UPDATE products SET quantity = quantity + :quantity WHERE partnumber = :partnumber");
            $addQuantity->execute(['quantity' => $quantity, 'partnumber' => $partnumber]);
            if($addQuantity){
                echo '<p>Inventory updated</p>';
            }else{
            echo '<p>Inventory not updated</p>';
            }
        }else{
                echo '<p>Please enter a valid quantity</p>';
        }
    }
    //Close the body and html tags after running the relevant functions.
    echo '
    </body>
</html>';
?>