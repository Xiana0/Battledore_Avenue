<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Battledore Avenue</title>
    <link rel="stylesheet" href="homestyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" href="Logo.png" type="image/png">
</head>

<body>
    <header>
        <div class="logo">
            <img src="Logo.png" alt="logo">
            <span>Battledore Avenue</span>
        </div>
        <div class="nav-icons">
            <input type="text" placeholder="search">

            <div class="user-menu-wrapper">
                <i class="fa-solid fa-user" id="userIcon" onclick="toggleUserMenu()" title="Account"></i>
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

        <div class="user-menu-wrapper">
            <div class="user-dropdown" id="userDropdownSide">
                <p class="user-name" id="displayNameSide">Guest</p>
                <a href="auth.php" id="loginLinkSide">LOGIN</a>
                <a href="booking.php">Book Court</a>
                <button id="logoutBtnSide" onclick="logoutUser()" style="display:none;">Logout</button>
            </div>
        </div>

        <a href="home.php">Home</a>
        <a href="booking.php">Book a Court</a>
        <a href="Jersey.php">Jersey</a>
        <a href="rentracket.php">Rent a racket</a>
        <a href="Accessories.php">Accessories</a>
    </div>

    <main style="padding: 40px; max-width: 800px; margin: auto;">
        <h1 style="color: #7b6db0; text-align: center;">Your Cart</h1>

        <div id="cartItems" style="background: white; padding: 20px; border-radius: 10px; margin: 20px 0;">
            <p style="text-align: center; color: #666;">Your cart is empty</p>
        </div>

        <div style="background: white; padding: 20px; border-radius: 10px; text-align: right;">
            <h2>Total: ₱<span id="totalPrice">0</span></h2>
            <button class="btn" onclick="goCheckout()"
                style="width: 200px; margin: auto; display: block;">Checkout</button>
        </div>
    </main>

    <footer>
        <div>📍 LOCATION
            <p>>Barangay 3, Public Market, San Mateo, Isabela</p>
        </div>
        <div>📞 CONTACTS
            <p><i class="fa-solid fa-phone"></i> 0965-048-5303</p>
            <p><i class="fa-solid fa-envelope"></i> manxanillobettyp@gmail.com</p>
            <p><a href="https://www.facebook.com/profile.php?id=61585019459424" target="_blank"><i
                        class="fa-brands fa-facebook"></i> Battledore Avenue</a></p>
            <p><a href="https://www.facebook.com/betty.pambid.50" target="_blank"><i class="fa-brands fa-facebook"></i>
                    Betty Pambid</a></p>
        </div>
    </footer>

    <script src="home.js"></script>
    <script src="cart.js"></script>
</body>

</html>