<!-- Initialize the blocks of HTML used for the interactive portions of the weight bracket interface. -->
<script>
    //HTML for adding a new weight bracket to the database.
    window.addNewBracket = "<p>\
        <form method=\"POST\" action=\"./weight.php\">\
            <label for=\"weight-min\" class=\"weight-elt\">\
                Minimum Weight:\
            </label>\
            <input type=\"text\" id=\"weight-min\" name=\"weight-min\" />\
            <label for=\"weight-max\" class=\"weight-elt\">\
                Maximum Weight:\
            </label>\
            <input type=\"text\" id=\"weight-max\" name=\"weight-max\" />\
            <label for=\"price\" class=\"weight-elt\">\
                Price:\
            </label>\
            <input type=\"text\" id=\"price\" name=\"price\" />\
            <div>\
                <input type=\"submit\" value=\"Add\" />\
            </div>\
        </form>\
    </p>"

    //HTML for updating the price of an existing weight bracket.
    window.updateBracket = "<p>\
        <form method=\"POST\" action=\"./weight.php\">\
            <label for=\"price\" class=\"weight-elt\">\
                New Price:\
            </label>\
            <input type=\"text\" id=\"price\" name=\"price\" />\
            <div>\
                <input type=\"submit\" value=\"Update\" />\
            </div>\
            <input type=\"hidden\" name=\"update\" value=\"true\" />\
        </form>\
    </p>"
</script>

<?php
    //TODO: Implement an interface to select an existing weight bracket.

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
        echo '<div class="weightbox">
            <div class="button-box">
                <button class="separate-button" onclick="getElementById(\'wt-opts\').innerHTML=window.updateBracket">
                    Alter existing brackets
                </button>
                <button class="separate-button" onclick="getElementById(\'wt-opts\').innerHTML=window.addNewBracket">
                    Add a new bracket
                </button>
            </div>
            <div id="wt-opts">';

            echo '</div>
        </div>';
    }

    //Close the body and html tags after running the relevant functions.
    echo '
    </body>
</html>';
?>