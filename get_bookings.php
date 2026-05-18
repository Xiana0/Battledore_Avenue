<?php

include "db.php";

$booking_date = $_GET['booking_date'];

$query = mysqli_query(

$conn,

"SELECT *

FROM bookings

WHERE booking_date='$booking_date'

AND status != 'Done'"

);

$bookings = [];

while($row = mysqli_fetch_assoc($query)){

    $bookings[] = $row;

}

echo json_encode($bookings);

?>