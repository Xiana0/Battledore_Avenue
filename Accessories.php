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
        <a href="rentracket.php">Rent a racket</a>
        <a href="Accessories.php">Accessories</a>
    </div>

    <section class="shop">

        <h1>Accessories</h1>

        <div class="product-grid">

            <div class="product">
                <img src="LD3 Qualifiers.jpg" alt="Jersey" class="product-img">

                <h3></h3>
                <p>₱50</p>

                <button onclick="openModal('Rawr', 50, 'LD3 Qualifiers.jpg')">
                    Add to Cart
                </button>
            </div>

            <div class="product">
                <img src="Summer camp 2026.jpg" alt="Jersey" class="product-img">

                <h3></h3>
                <p>₱50</p>

                <button class="btn" onclick="openModal('Summer Camp 2026', 50, 'Jersey.jpg')">
                    Add to Cart
                </button>
            </div>

            <div class="product">
                <img src="OZ Battledore Avenue Blue.jpg" alt="Jersey" class="product-img">

                <h3></h3>
                <p>₱50</p>

                <button class="btn"
                    onclick="openModal('OZ Battledore Avenue Blue', 50, 'OZ Battledore Avenue Blue.jpg')">
                    Add to Cart
                </button>
            </div>

            <div class="product">
                <img src="OZ Battledore Avenue Yellow.jpg" alt="Jersey" class="product-img">

                <h3></h3>
                <p>₱50</p>

                <button class="btn"
                    onclick="openModal('OZ Battledore Avenue Yellow', 50, 'OZ Battledore Avenue Yellow.jpg')">
                    Add to Cart
                </button>
            </div>

            <div class="product">
                <img src="OZ BattledoreAvenue 2025 (1).jpg" alt="Jersey" class="product-img">

                <h3></h3>
                <p>₱50</p>

                <button class="btn"
                    onclick="openModal('OZ Battledore Avenue 2025', 50, 'OZ BattledoreAvenue 2025 (1).jpg')">
                    Add to Cart
                </button>
            </div>

            <div class="product">
                <img src="OZ Purple Tech.jpg" alt="Jersey" class="product-img">

                <h3></h3>
                <p>₱50</p>

                <button class="btn" onclick="openModal('OZ Purple Tech', 50, 'OZ Purple Tech.jpg')">
                    Add to Cart
                </button>
            </div>

            <div class="product">
                <img src="OZ Whitesplash.jpg" alt="Jersey" class="product-img">

                <h3></h3>
                <p>₱50</p>

                <button class="btn" onclick="openModal('OZ Whitesplash', 50, 'OZ Whitesplash.jpg')">
                    Add to Cart
                </button>
            </div>

            <div class="product">
                <img src="Black&Purple.jpg" alt="Jersey" class="product-img">

                <h3></h3>
                <p>₱50</p>

                <button class="btn" onclick="openModal('Black & Purple', 50, 'Black&Purple.jpg')">
                    Add to Cart
                </button>
            </div>

            <div class="product">
                <img src="BlackRed.jpg" alt="Jersey" class="product-img">

                <h3></h3>
                <p>₱50</p>

                <button class="btn" onclick="openModal('Black & Red', 50, 'BlackRed.jpg')">
                    Add to Cart
                </button>
            </div>

            <div class="product">
                <img src="BlackRedBlue.jpg" alt="Jersey" class="product-img">

                <h3></h3>
                <p>₱50</p>

                <button class="btn" onclick="openModal('Black & Red & Blue', 50, 'BlackRedBlue.jpg')">
                    Add to Cart
                </button>
            </div>

            <div class="product">
                <img src="Predator.jpg" alt="Jersey" class="product-img">

                <h3></h3>
                <p>₱50</p>

                <button class="btn" onclick="openModal('Predator', 50, 'Predator.jpg')">
                    Add to Cart
                </button>
            </div>

            <div class="product">
                <img src="Battledore Avenue Gold&Black.jpg" alt="Jersey" class="product-img">

                <h3></h3>
                <p>₱50</p>

                <button class="btn"
                    onclick="openModal('Battledore Avenue Gold & Black', 50, 'Battledore Avenue Gold&Black.jpg')">
                    Add to Cart
                </button>
            </div>

        </div>

    </section>

    <div id="checkoutModal" class="modal">
        <div class="modal-box">
            <h2 id="modalTitle">Product Name</h2>
            <p>Price: ₱<span id="modalPrice"></span></p>

            <label>Name</label>
            <input type="text" id="customerName" placeholder="e.g. Juan Dela Cruz"><br><br>

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