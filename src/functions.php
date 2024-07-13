<?php

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
                <div class = "logopt">
                <input type="submit" value="Login" name="custlog" />
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
        <title>Parts</title>
        <link rel="stylesheet" href="./assets/style.css">
    </head>
    <body>
        <header>
            <h1>Parts Store</h1>
            <h2 class="header-band">
                <a href="./index.php" title="Home Page" class="header-links">&#x1F3E0;</a>' . "\n";
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
     */
    function passwordValidation() {
        //TODO: Convert the login logic to use PHP password hashing functions and access MariaDB.
        //TODO: Separate the logic for Admin and Employee logins and mark this status in the session.
        if(($_POST["username"] == "Admin" || $_POST["username"] == "Employee") && $_POST["password"] == "pass") {
            $_SESSION['username'] = $_POST["username"];
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
            die();
        }
    
        else {
            header("Location: ./index.php");
            die();
        }
    }
?>
