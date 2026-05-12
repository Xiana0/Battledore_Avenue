<?php

include "db.php";
session_start();

$user_id = $_SESSION['user_id'];

$court_name = $_POST['court_name'];
$booking_date = $_POST['booking_date'];
$booking_time = $_POST['booking_time'];

$sql = "INSERT INTO bookings
(user_id, court_name, booking_date, booking_time)

VALUES

('$user_id',
'$court_name',
'$booking_date',
'$booking_time')";

mysqli_query($conn, $sql);

echo "success";

?>