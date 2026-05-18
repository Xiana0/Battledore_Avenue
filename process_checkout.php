<?php

session_start();

include "db.php";

if (!isset($_SESSION['user_id'])) {

    echo "
    <script>

    alert('Please login first');

    window.location.href='auth.php';

    </script>
    ";

    exit();
}

$user_id = $_SESSION['user_id'];

$payment_method = "COD";

$total_amount = $_POST['total_amount'];

$cart = json_decode($_POST['cart'], true);


// BUILD ITEMS STRING

$items = "";

foreach ($cart as $item) {

    $items .= $item['name'];

    if (
        isset($item['color']) &&
        $item['color'] != ""
    ) {

        $items .= " (" .
            $item['color'] . ")";
    }

    if (
        isset($item['duration']) &&
        $item['duration'] != ""
    ) {

        $items .= " - " .
            $item['duration'] .
            " hour(s)";
    }

    $print_name = "";

    if (isset($item['print_name'])) {

        $print_name =
            $item['print_name'];
    }

    $print_number = "";

    if (isset($item['print_number'])) {

        $print_number =
            $item['print_number'];
    }

    $items .= ", ";
}


// SAVE ORDER

mysqli_query(

    $conn,

    "INSERT INTO orders(

user_id,
items,
total_amount,
payment_method,
payment_status

)

VALUES(

'$user_id',
'$items',
'$total_amount',
'$payment_method',
'Pending'

)"

);


// GET ORDER ID

$order_id = mysqli_insert_id($conn);


// SAVE ORDER ITEMS

foreach ($cart as $item) {

    $product_name = $item['name'];

    $price = $item['price'];

    $quantity = $item['quantity'];

    $size = "";

    if (isset($item['size'])) {

        $size = $item['size'];
    }

    $print_name = "";

    if (isset($item['print_name'])) {

        $print_name =
            $item['print_name'];
    }

    $print_number = "";

    if (isset($item['print_number'])) {

        $print_number =
            $item['print_number'];
    }

    // RENT RACKET

    if ($item['type'] == 'racket') {

        $duration = $item['duration'];

        mysqli_query(

            $conn,

            "INSERT INTO rent_rackets(

        order_id,
        user_id,
        racket_name,
        duration,
        price

        )

        VALUES(

        '$order_id',
        '$user_id',
        '$product_name',
        '$duration',
        '$price'

        )"

        );
    }

    // ACCESSORIES

    else if ($item['type'] == 'accessory') {
        isset($item['color']) &&
        $item['color'] != "";

        $color = $item['color'];

        mysqli_query(

            $conn,

            "INSERT INTO accessory_orders(

        order_id,
        user_id,
        accessory_name,
        color,
        price,
        quantity

        )

        VALUES(

        '$order_id',
        '$user_id',
        '$product_name',
        '$color',
        '$price',
        '$quantity'

        )"

        );
    }

    // JERSEYS

    else if ($item['type'] == 'jersey') {

        mysqli_query(

            $conn,

            "INSERT INTO order_items(

        order_id,
        product_name,
        print_name,
        print_number,
        size,
        price,
        quantity

        )

        VALUES(

        '$order_id',
        '$product_name',
        '$print_name',
        '$print_number',
        '$size',
        '$price',
        '$quantity'

        )"

        );
    }
}


echo "

<script>

localStorage.removeItem('cart');

alert('Payment Successful!');

window.location.href='home.php';

</script>

";
