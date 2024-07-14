<?php
    //Initialize the webpage with the appropriate statements to print the header and import relevant functions.
    session_start();
    require './src/functions.php';
    printHeader();

    //Initialize the flag for a successful login attempt.
    $successfulLogin = false;

    //If the user is already logged in, log them out.
    if(isset($_SESSION['username'])) {
        unset($_SESSION['username']);
        //TODO(possible): unset other relevant session variables.
        resetUserPage();
    }

    //If the user has attempted to log in, validate the login attempt.
    else if(isset($_POST["loginAttempt"])) {
        $successfulLogin = passwordValidation();
    }

    //If the user has not attempted to log in, or tried and failed, display the log in box.
    if(!$successfulLogin) {
        showLoginBox();
    }

    //If the user successfully logged in, move them to their prior page or the home page, as appropriate.
    else {
        resetUserPage();
    }

    //Close the body and html tags after running the relevant functions.
    echo '
    </body>
</html>'
?>