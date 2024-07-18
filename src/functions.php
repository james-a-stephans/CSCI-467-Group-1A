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
        <title>Parts</title>
        <link rel="stylesheet" href="./assets/style.css" />
    </head>
    <body>
        <header>
            <h1>Parts Store</h1>
            <h2 class="header-band">';
                if(isset($_SESSION['adminLogin'])) {
                    echo '<a href="./weight.php" title="Set Weight Price Brackets" class="header-links">&#x1F4E6;</a>' . "\n";
                }

                echo '<a href="./index.php" title="Home Page" class="header-links">&#x1F3E0;</a>' . "\n";
                echo '<a href="./parts.php" title="Products" class="header-links">&#x1FA9B;</a>' . "\n";
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
?>
