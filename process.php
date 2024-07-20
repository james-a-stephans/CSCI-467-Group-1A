<?php
    //Initialize the webpage with the appropriate statements to print the header and import relevant functions.
    session_start();
    require './src/functions.php';
    printHeader();

    //Set this page as the return page from the login page.
    $_SESSION['ReturnPage'] = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] .
    '?' . $_SERVER['QUERY_STRING'];

    //If the user is not an administrator, redirect them to the home page with an error message.
    if(!isset($_SESSION['username'])) {
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
    if(isset($_POST['submit'])){
        //logic to process delivery
    }
?>