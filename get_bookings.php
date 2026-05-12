<?php

include "db.php";

$booking_date = $_GET['booking_date'];

$sql = "SELECT * FROM bookings
WHERE booking_date='$booking_date'";

$result = mysqli_query($conn, $sql);

$bookings = [];

while($row = mysqli_fetch_assoc($result)){

    $bookings[] = $row;

}

echo json_encode($bookings);

?>