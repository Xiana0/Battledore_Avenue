<?php

session_start();

include "db.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Battledore Avenue</title>

    <link rel="stylesheet" href="homestyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" href="Logo.png">
</head>

<body>

    <header>
        <div class="logo">
            <img src="Logo.png">
            <span>Battledore Avenue</span>
        </div>

        <div class="nav-icons">
            <input type="text" placeholder="search">

            <div class="user-menu-wrapper">
                <i class="fa-solid fa-user" onclick="toggleUserMenu()"></i>
                <div class="user-dropdown" id="userDropdown">
                    <p id="displayName">
                        <?php
                        if (isset($_SESSION['user_name'])) {
                            echo $_SESSION['user_name'];
                        } else {
                            echo "Guest";
                        }
                        ?>
                    </p>

                    <?php if (isset($_SESSION['user_name'])) { ?>

                        <a href="logout.php">
                            <button>Logout</button>
                        </a>

                    <?php } else { ?>

                        <a href="auth.php">
                            <button>Login</button>
                        </a>

                    <?php } ?>
                </div>
            </div>

            <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i></a>
            <a href="#" onclick="openMenu()"><i class="fa-solid fa-bars"></i></a>
        </div>
    </header>


    <div id="sideMenu" class="side-menu">
        <span class="close-menu" onclick="closeMenu()">✖</span>

        <a href="home.php">Home</a>
        <a href="booking.php">Book a Court</a>
        <a href="Jersey.php">Jersey</a>
        <a href="rentracket.php">Rent a racket</a>
        <a href="Accessories.php">Accessories</a>
    </div>

    <main style="padding:40px; max-width:900px; margin:auto;">

        <h1 class="page-title" style="color:#7b6db0;">Checkout</h1>

        <form action="process_checkout.php" method="POST">

            <div class="booking-right" style="margin-bottom:20px;">
                <h3>Total Payment</h3>

                <p style="font-size:22px; font-weight:bold;">
                    ₱<span id="displayTotal">0</span>
                </p>
            </div>

            <div class="booking-left">

                <h2>Payment Method</h2>

                <input
                    type="text"
                    value="Cash on Delivery"
                    readonly

                    style="
        width:100%;
        padding:12px;
        text-align:center;
        font-weight:bold;
        background:#eee;
        border:none;
        border-radius:8px;
    ">

            </div>

            <div class="booking-right" style="margin-top:20px;">

                <button
                    class="btn"
                    type="submit">

                    Confirm Payment

                </button>

            </div>

        </form>

    </main>

    <footer>
        <div>📍 LOCATION
            <p>Barangay 3, Public Market, San Mateo, Isabela</p>
        </div>
        <div>📞 CONTACTS
            <p><i class="fa-solid fa-phone"></i> 0965-048-5303</p>
            <p><i class="fa-solid fa-envelope"></i> manxanillobettyp@gmail.com</p>
            <p><a href="https://www.facebook.com/profile.php?id=61585019459424" target="_blank"><i
                        class="fa-brands fa-facebook"></i> Battledore Avenue</a></p>
            <p></p><a href="https://www.facebook.com/betty.pambid.50" target="_blank"><i
                    class="fa-brands fa-facebook"></i> Betty Pambid</a></p>
        </div>
        </div>
    </footer>

    <script src="home.js"></script>
    <script src="checkout.js"></script>

    <script>
        let cart =
            JSON.parse(localStorage.getItem("cart")) || [];

        let total = 0;

        cart.forEach(item => {

            let quantity = item.quantity || 1;

            total += item.price * quantity;

        });

        document.getElementById("displayTotal")
            .innerText = total;

        document.getElementById("total_amount")
            .value = total;
    </script>

</body>

</html>