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
        unset($_SESSION['adminLogin']);
        resetUserPage();
    }

    //If the user has attempted to log in, validate the login attempt.
    else if(isset($_POST["loginAttempt"])) {
        //Fetch the user information associated with the given username.
        $result = $local_pdo->prepare("SELECT username, password, role FROM login WHERE username=?;");
        $result->execute(array($_POST['username']));
        $row = $result->fetch(PDO::FETCH_NUM);

        $successfulLogin = passwordValidation($row);

        //If the password hash is out of date, update it in the database.
        if(password_needs_rehash($loginInfo[1], 0)) {
            $newHash = password_hash($_POST['password'], 0);
            $queryStatement = "UPDATE login SET password='" . $newHash . "' WHERE password='" . $loginInfo[1] ."';";
            $local_pdo->exec($queryStatement);
        }
    }

    //If the user has not attempted to log in, or tried and failed, display the log in box.
    if(!$successfulLogin) {
        showLoginBox();

        if(isset($_POST["loginAttempt"])) {
            echo "\n<p class=\"login-error\">\nInvalid username or password.\n</p>\n";
        }
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