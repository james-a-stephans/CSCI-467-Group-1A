<!DOCTYPE html>
<html>
    <head><title>Shipping Label Generator</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+39&display=swap" rel="stylesheet">
        <style>
            .libre-barcode-39-regular {
                font-family: "Libre Barcode 39", system-ui;
                font-weight: 400;
                font-style: normal;
                text-align: center;
                font-size: 40px;
            }
        </style>
    </head>
    <body style="font-family: Arial, Helvetica, sans-serif;">
        <?php
            //Print where the priority box would be for a delivery-ready shipment.
            echo '<div style="border-width: 3px; border-style: solid; white-space: pre; width: 460px; height: 690px;">';
            echo '<div style="white-space: pre; width: 460px; height: 1in;">';
            echo '<div style="width: 1in; height: 100%; background-color: black;"></div>';
            echo '</div>';
            echo '<div style="white-space: pre; width: 460px; height: 40px; border-width: 1px; border-style: solid;"><h3 style="text-align: center;">FOR DEMONSTRATION PURPOSES ONLY</h3>
            </div>';

            //Print the name and address of the store and customer. The store address is currently a placeholder.
            echo '<div style="height: 50%; width: 100%; border-width: 1px; border-style: solid;">';
            echo '<p style="white-space: pre; font-size: 12px">DuCSS Parts Store                
452 Logan Lane
DeKalb, IL, 60115            
        </p><p style="text-align: right; font-size: 14px; font-weight: bold; margin-right: 48px;">RDC NA</p>
        
        
        
        <p style="white-space: pre; font-size: 15px; margin-left: 48px">' . $_POST['orderName'] . '
' . $_POST['orderSt'] . '
' . $_POST['orderCity'] .'
        </p>';
            
            //Print the tracking number and associated barcode.
            $trackingNum = str_pad($_POST['orderNo'], 22, "0", STR_PAD_LEFT);
            echo '</div>';
            echo '<p style="text-align: center">DEMONSTRATION TRACKING NUMBER</p>';
            echo '<p class="libre-barcode-39-regular">' . $trackingNum . '</p>';
            echo '<p style="text-align: center">' . $trackingNum . '</p>';
            echo '</div>';
        ?>
    </body>
</html>