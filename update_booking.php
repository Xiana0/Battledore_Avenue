<?php

include "db.php";

$booking_id = $_POST['booking_id'];

$status = $_POST['status'];

mysqli_query(

$conn,

"UPDATE bookings

SET status='$status'

WHERE id='$booking_id'"

);

header("Location: admin.php");

?>