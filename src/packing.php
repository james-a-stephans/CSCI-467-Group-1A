<!DOCTYPE html>
<html>
    <head><title>Packing List Generator</title>
        <style>
            .all-orders {
                margin: auto;
                width: 95%;
                border-style: none;
                text-align: center;
            }
            .orderhead {
                border-style: inset;
                border-width: 5px;
                border-color: black;
                text-align: center;
            }
            .part {
                border-style: double;
                border-width: 5px;
                border-color: black;
                color: black;
                text-align: center;
            }
            .order {
                border-style: none solid solid solid;
                border-color: black;
            }
        </style>
    </head>
    <body style="font-family: Arial, Helvetica, sans-serif;">
        <?php
            require './functions.php';

            //Print the order number header and table headers.
            echo '<h1 style="text-align: center;">Order #' . $_POST['orderNo'] .' Packing List</h1>';
            echo '<p><table class="all-orders">
                        <tr>
                            <th class="orderhead">Picture</th>
                            <th class="orderhead">Part Number</th>
                            <th class="orderhead">Description</th>
                            <th class="orderhead">Quantity</th>
                            <th class="orderhead">Unit Weight</th>
                            <th class="orderhead">Total Weight</th>
                        </tr>';

            //Fetch each item for the current order from the system database.
            $stmt = $local_pdo->prepare('SELECT partnumber, quantity FROM orders WHERE orderno = ?;');
            $stmt->execute(array($_POST['orderNo']));

            //Iterate through each item in the current order.
            while($row = $stmt->fetch()) {
                //Fetch the necessary information from the legacy database.
                $result = $pdo->prepare("SELECT pictureURL, description, weight FROM `parts` WHERE number=?;");
                $result->execute(array($row['partnumber']));
                $picture = $result->fetch(PDO::FETCH_NUM);

                //Print a row for the current item.
                echo "\n<tr class=\"part\">";
                echo '
                    <td class ="order"><img class="product-pic" src="' . $picture[0] . '"/></td>
                    <td class ="order">' . $row['partnumber'] . '</td>
                    <td class ="order">' . $picture[1] . '</td>
                    <td class ="order">' . $row['quantity'] . '</td>
                    <td class ="order">' . number_format($picture[2], 2) . '</td>
                    <td class ="order">' . number_format($picture[2] * $row['quantity'], 2) . '</td>';
                echo '</tr>';
            }

            echo '</table>';
        ?>
    </body>
</html>