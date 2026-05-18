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

        <h1>Jersey</h1>

        <div class="product-grid">

            <div class="product">
                <img src="LD3 Qualifiers.jpg" alt="Jersey" class="product-img">

                <h3>OZ San Mateo LD3 Jersey</h3>
                <p>₱500</p>

                <button onclick="openModal('San Mateo LD3 Jersey', 500, 'LD3 Qualifiers.jpg')">
                    Add to Cart
                </button>
            </div>

            <div class="product">
                <img src="Summer camp 2026.jpg" alt="Jersey" class="product-img">

                <h3>Summer Camp 2026</h3>
                <p>₱500</p>

                <button class="btn" onclick="openModal('Summer Camp 2026', 500, 'Jersey.jpg')">
                    Add to Cart
                </button>
            </div>

            <div class="product">
                <img src="OZ Battledore Avenue Blue.jpg" alt="Jersey" class="product-img">

                <h3>OZ Battledore Avenue Blue</h3>
                <p>₱500</p>

                <button class="btn"
                    onclick="openModal('OZ Battledore Avenue Blue', 500, 'OZ Battledore Avenue Blue.jpg')">
                    Add to Cart
                </button>
            </div>

            <div class="product">
                <img src="OZ Battledore Avenue Yellow.jpg" alt="Jersey" class="product-img">

                <h3>OZ Battledore Avenue Yellow</h3>
                <p>₱500</p>

                <button class="btn"
                    onclick="openModal('OZ Battledore Avenue Yellow', 500, 'OZ Battledore Avenue Yellow.jpg')">
                    Add to Cart
                </button>
            </div>

            <div class="product">
                <img src="OZ BattledoreAvenue 2025 (1).jpg" alt="Jersey" class="product-img">

                <h3>OZ Battledore Avenue 2025</h3>
                <p>₱500</p>

                <button class="btn"
                    onclick="openModal('OZ Battledore Avenue 2025', 500, 'OZ BattledoreAvenue 2025 (1).jpg')">
                    Add to Cart
                </button>
            </div>

            <div class="product">
                <img src="OZ Purple Tech.jpg" alt="Jersey" class="product-img">

                <h3>OZ Purple Tech</h3>
                <p>₱500</p>

                <button class="btn" onclick="openModal('OZ Purple Tech', 500, 'OZ Purple Tech.jpg')">
                    Add to Cart
                </button>
            </div>

            <div class="product">
                <img src="OZ Whitesplash.jpg" alt="Jersey" class="product-img">

                <h3>OZ Whitesplash</h3>
                <p>₱500</p>

                <button class="btn" onclick="openModal('OZ Whitesplash', 500, 'OZ Whitesplash.jpg')">
                    Add to Cart
                </button>
            </div>

            <div class="product">
                <img src="Black&Purple.jpg" alt="Jersey" class="product-img">

                <h3>OZ Black & Purple</h3>
                <p>₱500</p>

                <button class="btn" onclick="openModal('Black & Purple', 500, 'Black&Purple.jpg')">
                    Add to Cart
                </button>
            </div>

            <div class="product">
                <img src="BlackRed.jpg" alt="Jersey" class="product-img">

                <h3>OZ Black & Red</h3>
                <p>₱500</p>

                <button class="btn" onclick="openModal('Black & Red', 500, 'BlackRed.jpg')">
                    Add to Cart
                </button>
            </div>

            <div class="product">
                <img src="BlackRedBlue.jpg" alt="Jersey" class="product-img">

                <h3>OZ Black & Red & Blue</h3>
                <p>₱500</p>

                <button class="btn" onclick="openModal('Black & Red & Blue', 500, 'BlackRedBlue.jpg')">
                    Add to Cart
                </button>
            </div>

            <div class="product">
                <img src="Predator.jpg" alt="Jersey" class="product-img">

                <h3>OZ Predator</h3>
                <p>₱500</p>

                <button class="btn" onclick="openModal('Predator', 500, 'Predator.jpg')">
                    Add to Cart
                </button>
            </div>

            <div class="product">
                <img src="Battledore Avenue Gold&Black.jpg" alt="Jersey" class="product-img">

                <h3>Battledore Avenue Gold & Black</h3>
                <p>₱500</p>

                <button class="btn"
                    onclick="openModal('Battledore Avenue Gold & Black', 500, 'Battledore Avenue Gold&Black.jpg')">
                    Add to Cart
                </button>
            </div>

        </div>

    </section>

    <div id="checkoutModal" class="modal">
        <div class="modal-box">
            <h2 id="modalTitle">Product Name</h2>
            <p>Price: ₱<span id="modalPrice"></span></p>

            <label>Jersey Name</label>
            <input type="text" id="customerName" placeholder="e.g. Juan Dela Cruz">
            
            <label>Jersey Number</label>
            <input type="number" id="jerseyNumber" placeholder="e.g. 00"><br><br>

            <label>Size</label>
            <select id="customerSize">
                <option value="S">Small (S)</option>
                <option value="M">Medium (M)</option>
                <option value="L">Large (L)</option>
                <option value="XL">Extra Large (XL)</option>
            </select><br><br>

            <label>Contact Number</label>
            <input type="tel" id="customerContact" placeholder="e.g. 09XX-XXX-XXXX" maxlength="11"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>

            <label>Total</label>
            <input type="text" id="modalTotal" disabled>

            <div class="modal-buttons">
                <button class="btn" onclick="confirmAddToCart()">Confirm</button>
                <button class="cancel-btn" onclick="closeModal()">Cancel</button>
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
    <script src="jersey.js"></script>

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