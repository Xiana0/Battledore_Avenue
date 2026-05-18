<?php
session_start();

include "db.php";
require_once("send_otp.php");

if (isset($_POST['continue'])) {

    $email = trim($_POST['email']);

    $check = "SELECT * FROM users WHERE email=?";

    $stmt = $conn->prepare($check);

    $stmt->bind_param("s", $email);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $otp = rand(100000, 999999);

        $_SESSION['reset_otp'] = $otp;
        $_SESSION['reset_email'] = $email;

        if (sendEmail($email, "RESET PASSWORD OTP", "Your OTP is: " . $otp)) {

            echo "
            <script>
            alert('OTP has been sent');
            window.location.href='verify_otp.php';
            </script>
            ";
        } else {

            echo "
            <script>
            alert('Failed Sending OTP');
            </script>
            ";
        }
    } else {

        echo "
        <script>
        alert('Email not found!');
        </script>
        ";
    }
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Forgot Password</title>

    <style>
        body {

            display: flex;

            justify-content: center;

            align-items: center;

            height: 100vh;

            background: #dbefff;

            font-family: Arial;

        }

        .box {

            width: 380px;

            background: #7b6db0;

            padding: 35px;

            border-radius: 20px;

            text-align: center;

            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);

        }

        .logo img {

            width: 90px;

            margin-bottom: 10px;

        }

        .logo h2 {

            color: white;

            margin-bottom: 20px;

        }

        h3 {

            color: white;

            margin-bottom: 20px;

        }

        input {

            width: 100%;

            padding: 12px;

            margin-top: 10px;

            border: none;

            border-radius: 8px;

            box-sizing: border-box;

        }

        button {

            width: 100%;

            padding: 12px;

            margin-top: 15px;

            background: #0d6efd;

            color: white;

            border: none;

            border-radius: 8px;

            cursor: pointer;

            font-size: 16px;

        }
    </style>

</head>

<body>

    <div class="box">

        <div class="logo">

            <img src="Logo.png" alt="logo">

            <h2>
                Battledore Avenue
            </h2>

        </div>

        <h3>Forgot Password</h3>

        <form method="POST">

            <input
                type="email"
                name="email"
                placeholder="Enter your Email"
                required>

            <button
                type="submit"
                name="continue">

                Send OTP

            </button>

        </form>

    </div>

</body>

</html>