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

    //Display the interface to update the weight brackets for shipping prices.
    else {
        if(isset($_POST['task'])) {
            $checkWeight = $local_pdo->prepare("SELECT weight FROM `weightbrackets` WHERE weight=?;");
            $checkWeight->execute(array($_POST['weight-min']));
            $check = $checkWeight->fetch(PDO::FETCH_NUM);

            //If the user input was invalid, set that as a flag to be used later.
            if($_POST['task'] != "Remove" && (!is_numeric($_POST['price']) || $_POST['price'] < 0 ||
            !is_numeric($_POST['weight-min']) || ($_POST['task'] == "Add" && gettype($check) != "boolean") ||
            $_POST['weight-min'] < 0)) {
                $editSuccess = false;
            }

            //If the user input was valid, make the appropriate change.
            else {
                $editSuccess = true;

                if($_POST['task'] == "Add") {
                    $addWeight = $local_pdo->prepare("INSERT INTO weightbrackets (weight, price) VALUES (?, ?);");
                    $addWeight->execute(array($_POST['weight-min'], $_POST['price']));
                }

                else if($_POST['task'] == "Remove") {
                    $removeWeight = $local_pdo->prepare("DELETE FROM weightbrackets WHERE weight=?;");
                    $removeWeight->execute(array($_POST['weight-min']));
                }

                else {
                    $updateWeight = $local_pdo->prepare("UPDATE weightbrackets SET price=? WHERE weight=?;");
                    $updateWeight->execute(array($_POST['price'], $_POST['weight-min']));
                }
            }
        }

        //Fetch all of the existing weight brackets.
        $result = $local_pdo->query("SELECT weight, price FROM `weightbrackets`;");

        echo '<div class="weightbox">
            <div>
                <form method="GET" action="./weight.php" class="button-box">
                    <input type="submit" class="separate-button" value="Alter existing brackets" name="alter" />
                    <input type="submit" class="separate-button" value="Add a new bracket" name="add" />
                </form>
            </div>
            <div id="wt-opts">';

            //Display the interface to add a new weight bracket, or update an existing bracket, as appropriate.
            if(isset($_GET['add']) || isset($_GET['alter'])) {
                echo "<p>
                        <form method=\"POST\" action=\"./weight.php\">
                            <label for=\"weight-min\" class=\"weight-elt\">
                                Minimum Weight:
                            </label>";

                //If the user is adding a new weight, display an input box.
                if(isset($_GET['add'])) {
                    echo "<input type=\"text\" id=\"weight-min\" name=\"weight-min\" />";
                }

                //If the user is updating an existing weight, display a dropdown box of the existing weights.
                else {
                    echo "<select id=\"weight-min\" name=\"weight-min\">";

                    //Put all of the existing brackets in a dropdown menu.
                    while($row = $result->fetch(PDO::FETCH_NUM)) {
                        echo '<option value="' . $row[0] . '">' . $row[0] . ': Current price is $' . $row[1] . '</option>';
                    }

                    echo "</select>";
                }

                echo "<label for=\"price\" class=\"weight-elt\">
                                Price:
                            </label>
                            <input type=\"text\" id=\"price\" name=\"price\" />
                            <div>";

                //If the user is adding a new weight, display a button that says "Add".
                if(isset($_GET['add'])) {
                    echo "<input type=\"submit\" value=\"Add\" name=\"task\" />";
                }

                //If the user is updating an existing weight, display buttons that say "Update" and "Remove".
                else {
                    echo "<input type=\"submit\" value=\"Update\" name=\"task\" />";
                    echo "<input type=\"submit\" value=\"Remove\" name=\"task\" />";
                }

                echo "</div>
                        </form>
                    </p>";
            }

            echo '</div>
        </div>';

        if(isset($editSuccess)) {
            if($editSuccess) {
                echo '<p class="error-message">Alteration Successful.</p>';
            }

            else {
                echo '<p class="error-message">Alteration unsuccessful.</p>';
            }
        }
    }

    //Close the body and html tags after running the relevant functions.
    echo '
    </body>
</html>';
?>