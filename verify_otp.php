<?php

session_start();

if (isset($_POST['verify'])) {

    $otp = $_POST['otp'];

    if ($otp == $_SESSION['reset_otp']) {

        header("Location: reset_password.php");

        exit();
    } else {

        echo "
        <script>
        alert('Invalid OTP');
        </script>
        ";
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Verify OTP</title>

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

        <h2>Verify OTP</h2>

        <form method="POST">

            <input
                type="text"
                name="otp"
                placeholder="Enter OTP"
                required>

            <button
                type="submit"
                name="verify">

                Verify OTP

            </button>

        </form>

    </div>

</body>

</html>