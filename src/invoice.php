<!DOCTYPE html>
<html>
    <head><title>Invoice Generator</title>
        <style>
            .detailCol{
                width: auto;
                text-align: center;
            }
            .headerrow{
                background-color: lightblue;
            }
            tr, td, th {
                border-color: blue;
                border-width: 2px;
                border-style: solid;
            }
            .number {
                text-align: right;
            }
            .string {
                text-align: center;
            }
            .total {
                font-weight: bold;
                background-color: #9EC1E5;
            }
        </style>
    </head>
    <body style="font-family: Arial, Helvetica, sans-serif; margin: 1in; width: 8.5in;">
        <?php
            require './functions.php';

            //Print the invoice header.
            echo '<h5>INVOICE #' . $_POST['orderNo'] . '</h5>';

            //Print the store information.
            echo '<h1>DuCSS Parts Store</h1>';
            echo '<p style="white-space: pre; font-size: 12px;">452 Logan Lane
DeKalb, IL, 60115</p>';

            //Print the date.
            echo '<p style="white-space: pre; font-size: 12px; font-weight: bold;">' . date('m/d/Y') . '</p>';

            //Print the customer information.
            echo '<h3>TO</h3>';
            echo '<p style="white-space: pre; font-size: 12px;">' . $_POST['orderName'] . '
' . $_POST['orderSt'] . '
' . $_POST['orderCity'] .'
        </p>';

            //Print the order table headers.
            echo '<table style="width:95%;"><tr class="headerrow">
            <th class="detailCol">Quantity</th>
            <th class="detailCol">Item Number</th>
            <th class="detailCol">Description</th>
            <th class="detailCol">Unit Price</th>
            <th class="detailCol">Line Total</th></tr>';

            //Fetch each item for the current order from the system database.
            $stmt = $local_pdo->prepare('SELECT partnumber, quantity FROM orders WHERE orderno = ?;');
            $stmt->execute(array($_POST['orderNo']));

            //Initialize the subtotal.
            $subtotal = 0;

            //Iterate through each item in the current order.
            while($row = $stmt->fetch()) {
                //Fetch the necessary information from the legacy database.
                $result = $pdo->prepare("SELECT price, description FROM `parts` WHERE number=?;");
                $result->execute(array($row['partnumber']));
                $item = $result->fetch(PDO::FETCH_NUM);

                //Print a row for the current item.
                echo "\n<tr class=\"part\">";
                echo '
                    <td class ="number">' . $row['quantity'] . '</td>
                    <td class ="string">' . $row['partnumber'] . '</td>
                    <td class ="string">' . $item[1] . '</td>
                    <td class ="number">$' . number_format($item[0], 2) . '</td>
                    <td class ="number">$' . number_format($item[0] * $row['quantity'], 2) . '</td>';
                echo '</tr>';

                //Add the current item cost to the subtotal.
                $subtotal += $item[0] * $row['quantity'];
            }

            //Fetch the shipping and total cost from the order.
            $stmt = $local_pdo->prepare('SELECT shipping_cost, total_cost FROM orderInfo WHERE orderno = ?;');
            $stmt->execute(array($_POST['orderNo']));
            $result = $stmt->fetch();

            echo '<tr><td colspan="4" class="number">Subtotal</td><td>$' . number_format($subtotal, 2) . '</td>';
            echo '<tr><td colspan="4" class="number">Shipping Cost</td><td>$' . number_format($result[0], 2) . '</td>';
            echo '<tr class="total"><td colspan="4" class="number">Total</td><td>$' . number_format($result[1], 2) . '</td>';
            
            echo '</table>';
            echo '<h4>Thank you for your business!</h4>';
        ?>
    </body>
</html>