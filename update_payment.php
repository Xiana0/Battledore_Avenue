<?php

include "db.php";

$order_id = $_POST['order_id'];

$payment_status =
$_POST['payment_status'];

mysqli_query(

$conn,

"UPDATE orders

SET payment_status='$payment_status'

WHERE id='$order_id'"

);

header("Location: admin.php");

?>