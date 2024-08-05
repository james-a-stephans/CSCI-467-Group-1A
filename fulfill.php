<?php
    //Initialize the webpage with the appropriate statements to print the header and import relevant functions.
    session_start();
    require './src/functions.php';
    printHeader();

    //If the user is not a warehouse employee, redirect them to the home page with an error message.
    if(!isset($_SESSION['warehouseLogin'])) {
        $_SESSION['accessDenied'] = true;
        header("Location: ./index.php");
    }

    // Check if form was 
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['shipment_status'])) {
            foreach ($_POST['shipment_status'] as $orderno => $status) {
                if ($status == 'shipped') {
                    // updating order status
                    $dbstatus = $local_pdo->prepare("UPDATE orders SET status = 'Y' WHERE orderno = ?");
                    $dbstatus->execute([$orderno]);

                    // retrieving email from db
                    $email = $local_pdo->prepare("SELECT email FROM orders WHERE orderno = ?"); 
                    $email->execute([$orderno]);
                    $order = $email->fetch();

                    // sending email to notify status
                    $to = $order['email'];
                    $subject = "Your Order #" . $orderno . "has been shipped.";
                    $message = "Thank you for your order! It has been shipped and is on the way to you!";
                    $headers = "From: no-reply@carparts.com";

                    mail($to, $subject, $message, $headers);
                }
            }
        }
    }
    //Display the interface with options for viewing orders.
    echo '<div class="weightbox">
            <div>
                <form method="GET" action="./fulfill.php" class="button-box">
                    <input type="submit" class="seperate-button" value="View All Orders" name="all" />
                    <input type="submit" class="seperate-button" value="Search Orders" name="search" />
                </form>
            </div>';
        //Display the interface to view all orders or search for orders
        if(isset($_GET['all'])){
            echo '<p> All Orders </p>';
            $stmt = $local_pdo->prepare('SELECT * FROM orders ORDER BY orderno DESC');
            $stmt->execute();
            $orders = $stmt->fetchAll();
            showOrderTableFulfill($orders, $pdo);
        }
        //Display the interface to search for orders
        if(isset($_GET['search'])){
            echo '<p> Search Orders </p>';
            echo '<p><form method="POST" action="./fulfill.php?search=Search+Orders" class="weight-elt">
                <input type="radio" name="searchtype" value="orderno" checked /> Order Number
                <input type="radio" name="searchtype" value="email" /> Customer Email
                <input type="text" name="search"/>
                <input type="submit" name="submitsearch" /></form></p>';
            if(isset($_POST['submitsearch'])){
                echo '<p> Search Results </p>';
                $searchtype = $_POST['searchtype'];
                $search = $_POST['search'];
                if($searchtype == 'orderno' && !is_numeric($search)){
                    echo '<p> Please Enter a Valid Number </p>';
                }elseif($searchtype == 'email' && !filter_var($search, FILTER_VALIDATE_EMAIL)){
                    echo '<p> Invalid email address </p>';
                }else{
                    $stmt = $local_pdo->prepare('SELECT * FROM orders WHERE ' . $searchtype . ' = :search ORDER BY orderno DESC');
                    $stmt->execute(['search' => $search]);
                    $orders = $stmt->fetchAll();
                    showOrderTableFulfill($orders, $pdo);
                }
            }
        }

        //Close the body and html tags after running the relevant functions.
        echo '
        </body>
    </html>';
    ?>