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
    <link rel="icon" href="logo.png" type="image/png">
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
                <i class="fa-solid fa-user" id="userIcon" onclick="toggleUserMenu()"></i>

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
            <div class="user-dropdown" id="userDropdown">
                <p class="user-name" id="displayName">Guest</p>

                <a href="booking.php">Book Court</a>

                <a href="auth.php" id="loginLink">Login</a>

                <button id="logoutBtn" onclick="logoutUser()">Logout</button>
            </div>
        </div>

        <a href="home.php">Home</a>
        <a href="booking.php">Book a Court</a>
        <a href="Jersey.php">Jersey</a>
        <a href="rentracket.php">Rent a racket</a>
        <a href="Accessories.php">Accessories</a>
    </div>

    <section class="hero">
        <h1>WELCOME!</h1>
        <h3>TO</h3>
        <h2>BATTLEDORЕ AVENUE</h2>

        <div class="image-box">
            <div class="box">
                <img src="court.jpg" alt="Court">
            </div>

            <div class="box">
                <img src="banner.jpg" alt="Banner">
            </div>
        </div>

        <a href="booking.php"><button class="btn">BOOK NOW!</button></a>
    </section>

    <section class="offer">
        <h2>Join on this offer!</h2>

        <div class="offer-container">
            <div class="offer-text">
                <p>
                    SUMMER CAMP 2026 IS HERE!<br>
                    Train hard. Improve your skills. Compete and have fun!
                </p>

                <p>
                    Join the Battledore Avenue Summer Camp<br><br>
                    April 20 – 29<br>
                    Open for Beginner & Intermediate Players<br><br>
                    9 Days Intensive Training + 1 Day Tournament<br><br>
                    Limited slots only! Register now!
                </p>
            </div>

            <div class="offer-images">
                <img src="camp.jpg" alt="camp poster">
            </div>

            <div class="offer-image2">
                <img src="Summer camp 2026.jpg" alt="jersey">
            </div>
        </div>

        <a href="https://www.facebook.com/profile.php?id=61585019459424" target="_blank">
            <button class="btn">Register now!</button>
        </a>
    </section>

    <footer>
        <div>📍 LOCATION

            <p><i class="fa-solid fa-map-marker-alt"></i><a
                    href="https://www.google.com/maps/@16.8814663,121.5879764,3a,75y,345.04h,78.67t/data=!3m7!1e1!3m5!1sIK7igqjx6mr8Hfe1Vkz44A!2e0!6shttps:%2F%2Fstreetviewpixels-pa.googleapis.com%2Fv1%2Fthumbnail%3Fcb_client%3Dmaps_sv.tactile%26w%3D900%26h%3D600%26pitch%3D11.330046098314597%26panoid%3DIK7igqjx6mr8Hfe1Vkz44A%26yaw%3D345.03528244071964!7i16384!8i8192?entry=ttu&g_ep=EgoyMDI2MDUwMi4wIKXMDSoASAFQAw%3D%3D"
                    target="_blank">
                    Barangay 3, Public Market, San Mateo, Isabela
                </a></p>
        </div>
        <div>📞 CONTACTS
            <p><i class="fa-solid fa-phone"></i> 0965-048-5303</p>
            <p><i class="fa-solid fa-envelope"></i> manxanillobettyp@gmail.com</p>
            <p><a href="https://www.facebook.com/profile.php?id=61585019459424" target="_blank"><i
                        class="fa-brands fa-facebook"></i> Battledore Avenue</a></p>
            <p></p><a href="https://www.facebook.com/betty.pambid.50" target="_blank"><i
                    class="fa-brands fa-facebook"></i> Betty Pambid</a></p>
        </div>
    </footer>

    <script>
        function openMenu() {
            document.getElementById("sideMenu").style.right = "0";
        }

        function closeMenu() {
            document.getElementById("sideMenu").style.right = "-250px";
        }
    </script>

    <script src="home.js"></script>

</body>

</html>