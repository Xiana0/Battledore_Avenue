<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

function sendEmail($to, $subject, $message){

    $mail = new PHPMailer(true);

    try{

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

        $mail->addAddress($to);

        $mail->isHTML(true);

        $mail->Subject = $subject;

        $mail->Body = $message;

        $mail->send();

        return true;

    }catch(Exception $e){

        return false;

    }

}