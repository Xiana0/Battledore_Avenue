<?php

session_start();

include "db.php";

// CHECK LOGIN
if(!isset($_SESSION['user_id'])){

    echo "not_logged_in";

    exit();

}

// GET USER ID
$user_id = $_SESSION['user_id'];

// GET BOOKING DATA
$court_name = $_POST['court_name'];

$booking_date = $_POST['booking_date'];

$booking_time = $_POST['booking_time'];

$status = "Pending";


// INSERT BOOKING
$query = mysqli_query(

$conn,

"INSERT INTO bookings(

user_id,
court_name,
booking_date,
booking_time,
status

)

VALUES(

'$user_id',
'$court_name',
'$booking_date',
'$booking_time',
'$status'

)"

);


// CHECK SUCCESS
if($query){

    echo "success";

}else{

    echo mysqli_error($conn);

}

?>