<?php
include "db.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Battledore Avenue</title>
    <link rel="stylesheet" href="auth.css">
    <link rel="icon" href="Logo.png" type="image/png">
</head>

<body>

    <div class="container">

        <a href="home.php" class="close-btn">✖</a>

        <div class="logo">
            <img src="Logo.png" alt="logo">
            <span>Battledore Avenue</span>
        </div>

        <div class="tabs">
            <button onclick="showForm('login')" id="loginTab">Login</button>
            <button onclick="showForm('admin')" id="adminTab">Admin Login</button>
        </div>

        <div id="login" class="form active">
            <form action="login.php" method="post">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter your email" required>

                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
                <div class="row">
                    <label class="remember">
                        <input type="checkbox">
                        <span>Remember me</span>
                    </label>
                    <a href="forgot_password.php">Forgot Password?</a>
                </div>

                <button class="btn" type="submit">Login</button>

                <p class="switch">Don't have an account?
                    <span onclick="showForm('register')">Register here!</span>
                </p>
            </form>
        </div>

        <div id="admin" class="form">
            <form action="admin_login.php" method="post">
                <label>ID Number</label>
                <input type="text" name="admin_id" placeholder="ID number" required>

                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>

                <div class="row">
                    <label class="remember">
                        <input type="checkbox">
                        <span>Remember me</span>
                    </label>
                    <a href="forgot_password.php">Forgot Password?</a>
                </div>

                <button class="btn" type="submit">Admin Login</button>
            </form>
        </div>

        <div id="register" class="form">
            <form action="register.php" method="post">
                <h3>Registration</h3>

                <label>Full Name</label>
                <input type="text" name="name" required>

                <label>Email Address</label>
                <input type="email" name="email" required>

                <label>Address</label>
                <input type="address" name="address" required>

                <label>Contact Number</label>
                <input type="tel" name="contact" placeholder="e.g. 09XX-XXX-XXXX" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>

                <label>Password</label>
                <input type="password" name="password" required>


                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>

                <div class="terms">
                    <label class="terms right">
                        <span>I agree to the Terms and Conditions</span>
                        <input type="checkbox" id="termsCheck">
                    </label>
                </div>

               <button class="btn" type="submit">Register</button>

                <p class="switch">Already have an account?
                    <span onclick="showForm('login')">Login here!</span>
                </p>
            </form>
        </div>

    </div>

    <script src="auth.js"></script>
</body>

</html>