<?php
session_start();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <title>Battledore Avenue</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="bookingstyle.css">
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
        <a href="rentracket.php">Rent a racket & Accessories</a>
    </div>

    <main>
        <section id="screen1" class="screen active">
            <h1 class="page-title">Booking</h1>
            <div class="courts-grid">
                <div class="court-card">
                    <div class="court-name">Court 1</div>
                    <button class="btn-purple" onclick="selectCourt('Court 1')">Select Court</button>
                </div>
                <div class="court-card">
                    <div class="court-name">Court 2</div>
                    <button class="btn-purple" onclick="selectCourt('Court 2')">Select Court</button>
                </div>
                <div class="court-card center-card">
                    <div class="court-name">Court 3</div>
                    <button class="btn-purple" onclick="selectCourt('Court 3')">Select Court</button>
                </div>
            </div>
        </section>

        <section id="screen2" class="screen">
            <h1 class="page-title">Booking</h1>
            <div class="booking-layout">
                <div class="booking-left">
                    <h2>Date & Time Selection</h2>
                    <label for="bookingDate">Select Date:</label>
                    <input type="date"
                        id="bookingDate"
                        onchange="updateDetails(); loadBookedSlots()">
                    <div class="time-slots">
                        <button class="time-btn available" onclick="selectTime('07:00 AM - 8:00 AM', this)">07:00 AM - 8:00 AM</button>
                        <button class="time-btn available" onclick="selectTime('08:00 AM - 9:00 AM', this)">08:00 AM - 9:00 AM</button>
                        <button class="time-btn available" onclick="selectTime('09:00 AM - 10:00 AM', this)">09:00 AM - 10:00 AM</button>
                        <button class="time-btn available" onclick="selectTime('10:00 AM - 11:00 AM', this)">10:00 AM - 11:00 AM</button>
                        <button class="time-btn available" onclick="selectTime('11:00 AM - 12:00 PM', this)">11:00 AM - 12:00 PM</button>
                        <button class="time-btn available" onclick="selectTime('1:00 PM - 2:00 PM', this)">1:00 PM - 2:00 PM</button>
                        <button class="time-btn available" onclick="selectTime('2:00 PM - 3:00 PM', this)">2:00 PM - 3:00 PM</button>
                        <button class="time-btn available" onclick="selectTime('4:00 PM - 5:00 PM', this)">4:00 PM - 5:00 PM</button>
                        <button class="time-btn available" onclick="selectTime('5:00 PM - 6:00 PM', this)">5:00 PM - 6:00 PM</button>
                        <button class="time-btn available" onclick="selectTime('6:00 PM - 7:00 PM', this)">6:00 PM - 7:00 PM</button>
                        <button class="time-btn available" onclick="selectTime('7:00 PM - 8:00 PM', this)">7:00 PM - 8:00 PM</button>
                        <button class="time-btn available" onclick="selectTime('8:00 PM - 9:00 PM', this)">8:00 PM - 9:00 PM</button>
                    </div>
                </div>
                <div class="booking-right">
                    <h3>Booking Details</h3>
                    <div class="booking-details">
                        <p id="detailCourt">Court: -</p>
                        <p id="detailDate">Date: -</p>
                        <p id="detailTime">Time: -</p>
                    </div>
                    <button class="confirm-btn" onclick="confirmBooking()">Confirm Booking</button>
                </div>
            </div>
        </section>

        <section id="screen3" class="screen">
            <h1 class="page-title">Booking</h1>
            <div class="confirmation-wrapper">
                <div class="confirmation-box">
                    <h2>✅ Booking Confirmed!</h2>
                    <p>Your booking has been<br>successfully placed.</p>
                    <p>Details will be sent to your email.</p>
                </div>
                <a href="home.php">
                    <button class="back-btn">Back to Home</button>
                </a>
            </div>
        </section>
    </main>

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
            <p><a href="https://www.facebook.com/profile.php?id=61585019459424" target="_blank"><i class="fa-brands fa-facebook"></i> Battledore Avenue</a></p>
            <p><a href="https://www.facebook.com/betty.pambid.50" target="_blank"><i class="fa-brands fa-facebook"></i> Betty Pambid</a></p>
        </div>
    </footer>

    <script src="booking.js"></script>
</body>

</html>