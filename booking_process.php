<?php

include "db.php";

session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';


$user_id = $_SESSION['user_id'];

$court_name = $_POST['court_name'];

$booking_date = $_POST['booking_date'];

$booking_time = $_POST['booking_time'];


// GET USER INFO

$getUser = mysqli_query(

$conn,

"SELECT * FROM users
WHERE id='$user_id'"

);

$user = mysqli_fetch_assoc($getUser);

$user_email = $user['email'];

$user_name = $user['fullname'];


// SAVE BOOKING

$sql = "INSERT INTO bookings

(user_id, court_name, booking_date, booking_time, status)

VALUES

('$user_id',
'$court_name',
'$booking_date',
'$booking_time',
'Reserved')";


$query = mysqli_query($conn, $sql);

if($query){


    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();

        $mail->Host = 'smtp.gmail.com';

        $mail->SMTPAuth = true;

        $mail->Username = 'katrinajessicamanzanillo1325@gmail.com';

        $mail->Password = 'nmlmeottytxjujbz';

        $mail->SMTPSecure = 'tls';

        $mail->Port = 587;

        $mail->setFrom(

            'katrinajessicamanzanillo1325@gmail.com',

            'Battledore Avenue'

        );

        $mail->addAddress($user_email);

        $mail->isHTML(true);

        $mail->Subject =
        'Booking Confirmation';

        $mail->Body = "

        <h2>Booking Confirmed!</h2>

        <p>Hello $user_name,</p>

        <p>Your booking has been confirmed.</p>

        <p><b>Court:</b> $court_name</p>

        <p><b>Date:</b> $booking_date</p>

        <p><b>Time:</b> $booking_time</p>

        <br>

        <p>Thank you for choosing
        Battledore Avenue 🏸</p>

        ";

        $mail->send();

        echo "success";

    } catch (Exception $e) {

        echo $mail->ErrorInfo;

    }

} else{

    echo mysqli_error($conn);

}

?>