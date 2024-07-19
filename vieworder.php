<?php
    //Initialize the webpage with the appropriate statements to print the header and import relevant functions.
    session_start();
    require './src/functions.php';
    printHeader();

    //Set this page as the return page from the login page.
    $_SESSION['ReturnPage'] = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] .
    '?' . $_SERVER['QUERY_STRING'];

    //If the user is not an administrator, redirect them to the home page with an error message.
    if(!isset($_SESSION['adminLogin'])) {
        $_SESSION['accessDenied'] = true;
        header("Location: ./index.php");
    }

    //Display the interface with options for viewing orders.
    else{
        echo '<div class="weightbox">
            <div>
                <form method="GET" action="./vieworder.php" class="button-box">
                    <input type="submit" class="seperate-button" value="View All Orders" name="all" />
                    <input type="submit" class="seperate-button" value="Search Orders" name="search" />
                </form>
            </div>';
        //Display the interface to view all orders or search for orders
        if(isset($_GET['all'])){
            echo '<p> All Orders </p>';
            //TODO: get part name and price for orders
            $stmt = $local_pdo->prepare('SELECT * FROM orders');
            $stmt->execute();
            $orders = $stmt->fetchAll();
            showOrderTable($orders, $pdo);
        }
        //Display the interface to search for orders
        if(isset($_GET['search'])){
            echo '<p> Search Orders </p>';
            echo '<p><form method="POST" action="./vieworder.php?search=Search+Orders" class="weight-elt">
                <input type="radio" name="searchtype" value="orderno" checked /> Order Number
                <input type="radio" name="searchtype" value="email" /> Customer Email
                <input type="text" name="search"/>
                <input type="submit" name="submitsearch" /></form></p>';
            if(isset($_POST['submitsearch'])){
                echo '<p> Search Results </p>';
                $searchtype = $_POST['searchtype'];
                $search = $_POST['search'];
                $stmt = $local_pdo->prepare('SELECT * FROM orders WHERE ' . $searchtype . ' = :search');
                $stmt->bindParam(':search', $search);
                $stmt->execute();
                $orders = $stmt->fetchAll();
                showOrderTable($orders, $pdo);
            }
        }
    }
?>