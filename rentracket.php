<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Battledore Avenue</title>

    <link rel="stylesheet" href="homestyle.css">
    <link rel="stylesheet" href="jerseystyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" href="logo.png" type="image/png">
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

            <i class="fa-solid fa-bars" onclick="openMenu()"></i>
        </div>
    </header>

    <div id="sideMenu" class="side-menu">
        <span class="close-menu" onclick="closeMenu()">✖</span>

        <a href="home.php">Home</a>
        <a href="booking.php">Book a Court</a>
        <a href="Jersey.php">Jersey</a>
        <a href="rentracket.php">Rent a racket & Accessories</a>
    </div>

    <section class="shop">

        <h1>Rent a Racket</h1>

        <div class="product-grid">

            <div class="product">
                <img src="ALPSPORT RR Badminton.jpg" alt="Racket" class="product-img">
                <h3>ALPSPORT RR Badminton</h3>
                <p>₱50</p>
                <button onclick="openModal('ALPSPORT RR Badminton', 50, 'ALPSPORT RR Badminton.jpg', false, true)">
                    Rent
                </button>
            </div>

            <div class="product">
                <img src="Li-Ning Axforce 10 Badminton Racket.jpg" alt="Racket" class="product-img">
                <h3>Li-Ning Axforce 10</h3>
                <p>₱50</p>
                <button class="btn" onclick="openModal('Li-Ning Axforce 10', 50, 'Li-Ning Axforce 10 Badminton Racket.jpg', false, true)">
                    Rent
                </button>
            </div>

            <div class="product">
                <img src="ULTRAMAX STRIKE FORCE.jpg" alt="Racket" class="product-img">
                <h3>Ultramax strike force</h3>
                <p>₱50</p>
                <button class="btn" onclick="openModal('Ultramax strike force', 50, 'ULTRAMAX STRIKE FORCE.jpg', false, true)">
                    Rent
                </button>
            </div>

            <div class="product">
                <img src="Yonex ArcSaber 11 Play.jpg" alt="Racket" class="product-img">
                <h3>Yonex ArcSaber 11 Play</h3>
                <p>₱50</p>
                <button class="btn" onclick="openModal('Yonex ArcSaber 11 Play', 50,'Yonex ArcSaber 11 Play.jpg',false, true)">
                    Rent
                </button>
            </div>
        </div>


        <h1 style="margin-top:60px;">

            Accessories

        </h1>

        <div class="product-grid">

            <div class="product">
                <img src="Yonex Grip.png" alt="Accessory" class="product-img">
                <h3>Yonex Grip</h3>
                <p>₱50</p>
                <button class="btn" onclick="openModal('Yonex Grip', 50, 'Yonex Grip.png', true, false)">
                    Add to Cart
                </button>
            </div>

            <div class="product">
                <img src="String.jpg" alt="Accessory" class="product-img">
                <h3>String</h3>
                <p>₱500</p>
                <button class="btn" onclick="openModal('String', 500, 'String.jpg', false, false)">
                    Add to Cart
                </button>
            </div>

            <div class="product">
                <img src="Kaichi Shuttlecock.jpg" alt="Accessory" class="product-img">
                <h3>Kaichi Shuttlecock</h3>
                <p>₱120</p>
                <button class="btn" onclick="openModal('Kaichi Shuttlecock', 120, 'Kaichi Shuttlecock.jpg', false, false)">
                    Add to Cart
                </button>
            </div>

        </div>

    </section>

    <div id="checkoutModal" class="modal">

        <div class="modal-box">

            <h2 id="modalTitle">Product Name</h2>

            <p>
                Price: ₱<span id="modalPrice"></span>
            </p>

            <label>Full Name</label>

            <input
                type="text"
                id="customerName"
                placeholder="e.g. Juan Dela Cruz">


            <label>Contact Number</label>

            <input
                type="tel"
                id="customerContact"
                placeholder="e.g. 09XX-XXX-XXXX"
                maxlength="11"

                oninput="
        this.value =
        this.value.replace(/[^0-9]/g, '')
        "

                required>


            <!-- RENTAL SECTION -->

            <div id="durationSection">

                <label>
                    Rental Duration (Hour/s)
                </label>

                <input
                    type="text"
                    id="rentingday"
                    placeholder="e.g. 1">

            </div>


            <!-- COLOR SECTION -->

            <div id="colorSection">

                <label>Select Color</label>

                <select id="productColor">

                    <option value="Purple">
                        Purple
                    </option>

                    <option value="Black">
                        Black
                    </option>

                    <option value="White">
                        White
                    </option>

                    <option value="Orange">
                        Orange
                    </option>

                    <option value="Green">
                        Green
                    </option>
                    
                    <option value="Blue">
                        Blue
                    </option>
                </select>
                

            </div>


            <label>Total</label>

            <input
                type="text"
                id="modalTotal"
                disabled>


            <div class="modal-buttons">

                <button
                    class="btn"
                    onclick="confirmAddToCart()">

                    Confirm

                </button>

                <button
                    class="cancel-btn"
                    onclick="closeModal()">

                    Cancel

                </button>

            </div>

        </div>

    </div>
    </div>

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

    <script src="home.js"></script>
    <script src="Jersey.js"></script>

    <script>
        function openMenu() {
            document.getElementById("sideMenu").style.right = "0";
        }

        function closeMenu() {
            document.getElementById("sideMenu").style.right = "-250px";
        }
    </script>


</body>

</html>