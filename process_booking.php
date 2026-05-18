<?php

session_start();

include "db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';


// CHECK LOGIN

if (!isset($_SESSION['user_id'])) {

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


// GET USER INFO

$getUser = mysqli_query(

    $conn,

    "SELECT * FROM users
WHERE id='$user_id'"

);

$user =
    mysqli_fetch_assoc($getUser);

$user_email =
    $user['email'];

$user_name =
    $user['fullname'];


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


if ($query) {

    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();

        $mail->Host = 'smtp.gmail.com';

        $mail->SMTPAuth = true;

        $mail->Username =
            'manzanillokatrina1325@gmail.com';

        $mail->Password =
            'kify vcpp dotp zlho';

        $mail->SMTPSecure = 'tls';

        $mail->Port = 587;

        $mail->setFrom(

            'manzanillokatrina1325@gmail.com',

            'Battledore Avenue'

        );

        $mail->addAddress($user_email);

        $mail->isHTML(true);

        $mail->Subject =
            'Court Booking Confirmation';

        $mail->Body = "

        <h2>Booking Confirmed 🏸</h2>

        <p>Hello $user_name,</p>

        <p>Your booking has been confirmed.</p>

        <hr>

        <p>
        <b>Court:</b>
        $court_name
        </p>

        <p>
        <b>Date:</b>
        $booking_date
        </p>

        <p>
        <b>Time:</b>
        $booking_time
        </p>

        <hr>

        <p>
        Thank you for booking at
        Battledore Avenue!
        </p>

        ";

        $mail->send();
    } catch (Exception $e) {

        echo $mail->ErrorInfo;
    }

    echo "success";
} else {

    echo mysqli_error($conn);
}
