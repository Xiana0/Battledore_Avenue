<?php

session_start();
include "db.php";

// PROTECT ADMIN PAGE
if (!isset($_SESSION['admin'])) {

    header("Location: auth.php");
    exit();
}

// TOTAL BOOKINGS
$bookings = mysqli_num_rows(
    mysqli_query(
        $conn,
        "SELECT * FROM bookings"
    )
);

// TOTAL USERS
$customers = mysqli_num_rows(
    mysqli_query(
        $conn,
        "SELECT * FROM users"
    )
);

// TOTAL ORDERS
$orders = mysqli_num_rows(
    mysqli_query(
        $conn,
        "SELECT * FROM orders"
    )
);

// TOTAL REVENUE
$revenueQuery = mysqli_query(

    $conn,

    "SELECT SUM(total_amount)
    AS total
    FROM orders"

);

$revenueData =
    mysqli_fetch_assoc($revenueQuery);

$revenue = $revenueData['total'];


// RECENT BOOKINGS
$bookingQuery = mysqli_query(

    $conn,

    "SELECT bookings.*,
    users.fullname

    FROM bookings

    LEFT JOIN users

    ON bookings.user_id = users.id

    ORDER BY bookings.id DESC"

);


// USERS
$userQuery = mysqli_query(

    $conn,

    "SELECT * FROM users"

);


// ORDERS
$orderQuery = mysqli_query(

    $conn,

    "SELECT orders.*,
    users.fullname

    FROM orders

    LEFT JOIN users

    ON orders.user_id = users.id

    ORDER BY orders.id DESC"

);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Admin Dashboard</title>

    <link rel="stylesheet" href="admin.css">

    <link rel="icon" href="Logo.png" type="image/png">

</head>

<body>

    <header>

        <h2>🏸 Battledore Avenue Admin</h2>

        <div>

            Logged in as:
            <?php echo $_SESSION['admin']; ?>

            |

            <a class="logout" href="logout.php">
                Logout
            </a>

        </div>

    </header>


    <div class="dashboard">

        <!-- DASHBOARD CARDS -->

        <div class="cards">

            <div class="card">

                <h3>Total Bookings</h3>

                <p><?php echo $bookings; ?></p>

            </div>

            <div class="card">

                <h3>Total Customers</h3>

                <p><?php echo $customers; ?></p>

            </div>

            <div class="card">

                <h3>Total Orders</h3>

                <p><?php echo $orders; ?></p>

            </div>

            <div class="card">

                <h3>Revenue</h3>

                <p>
                    ₱<?php echo number_format($revenue); ?>
                </p>

            </div>

        </div>

        <!-- BOOKINGS -->

        <h2>Recent Bookings</h2>

        <table>

            <tr>

                <th>Customer</th>
                <th>Court</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>

            </tr>

            <?php
            while (
                $row =
                mysqli_fetch_assoc($bookingQuery)
            ) {
            ?>

                <tr>

                    <td>
                        <?php echo $row['fullname']; ?>
                    </td>

                    <td>
                        <?php echo $row['court_name']; ?>
                    </td>

                    <td>

                        <?php

                        $bookingDate =
                            strtotime($row['booking_date']);

                        $today =
                            date("Y-m-d");

                        $yesterday =
                            date(
                                "Y-m-d",
                                strtotime("-1 day")
                            );

                        if ($row['booking_date'] == $today) {

                            echo "Today";
                        } else if (
                            $row['booking_date'] == $yesterday
                        ) {

                            echo "Yesterday";
                        } else {

                            echo date(
                                "F d, Y",
                                $bookingDate
                            );
                        }

                        ?>

                    </td>

                    <td>
                        <?php echo $row['booking_time']; ?>
                    </td>

                    <td>

                        <form action="update_booking.php" method="POST">

                            <input
                                type="hidden"
                                name="booking_id"
                                value="<?php echo $row['id']; ?>">

                            <select name="status">

                                <option value="Pending"
                                    <?php
                                    if ($row['status'] == "Pending") {
                                        echo "selected";
                                    }
                                    ?>>
                                    Pending
                                </option>

                                <option value="Playing"
                                    <?php
                                    if ($row['status'] == "Playing") {
                                        echo "selected";
                                    }
                                    ?>>
                                    Playing
                                </option>

                                <option value="Done"
                                    <?php
                                    if ($row['status'] == "Done") {
                                        echo "selected";
                                    }
                                    ?>>
                                    Done
                                </option>

                            </select>

                            <button type="submit">

                                Update

                            </button>

                        </form>

                    </td>

                </tr>

            <?php } ?>

        </table>



        <!-- CUSTOMERS -->

        <h2 style="margin-top:50px;">
            Customers
        </h2>

        <table>

            <tr>

                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Contact</th>
                <th>Address</th>

            </tr>

            <?php
            while (
                $user =
                mysqli_fetch_assoc($userQuery)
            ) {
            ?>

                <tr>

                    <td>
                        <?php echo $user['id']; ?>
                    </td>

                    <td>
                        <?php echo $user['fullname']; ?>
                    </td>

                    <td>
                        <?php echo $user['email']; ?>
                    </td>

                    <td>
                        <?php echo $user['contact']; ?>
                    </td>

                    <td>
                        <?php echo $user['address']; ?>
                    </td>

                </tr>

            <?php } ?>

        </table>

        <h2 style="margin-top:50px;">
            Sales Summary
        </h2>

        <?php


        $todaySalesQuery = mysqli_query(

            $conn,

            "SELECT SUM(total_amount) AS total
            FROM orders
            WHERE DATE(created_at) = CURDATE()"

        );

        $todaySalesData =
            mysqli_fetch_assoc($todaySalesQuery);

        $todaySales =
            $todaySalesData['total'];



        $paidSalesQuery = mysqli_query(

            $conn,

            "SELECT SUM(total_amount) AS total
            FROM orders
            WHERE payment_status = 'Paid'"

        );

        $paidSalesData =
            mysqli_fetch_assoc($paidSalesQuery);

        $paidSales =
            $paidSalesData['total'];



        $pendingSalesQuery = mysqli_query(

            $conn,

            "SELECT SUM(total_amount) AS total
            FROM orders
            WHERE payment_status = 'Pending'"

        );

        $pendingSalesData =
            mysqli_fetch_assoc($pendingSalesQuery);

        $pendingSales =
            $pendingSalesData['total'];

        ?>

        <div class="sales-container">

            <div class="sales-card">

                <h3>Today's Sales</h3>

                <p>
                    ₱<?php echo number_format($todaySales); ?>
                </p>

            </div>

            <div class="sales-card">

                <h3>Paid Sales</h3>

                <p>
                    ₱<?php echo number_format($paidSales); ?>
                </p>

            </div>

            <div class="sales-card">

                <h3>Pending Sales</h3>

                <p>
                    ₱<?php echo number_format($pendingSales); ?>
                </p>

            </div>

        </div>

        <h2 style="margin-top:50px;">
            Orders
        </h2>

        <table>

            <tr>

                <th>Order ID</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Items</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Action</th>

            </tr>

            <?php
            while (
                $order =
                mysqli_fetch_assoc($orderQuery)
            ) {
            ?>

                <tr>

                    <td>
                        <?php echo $order['id']; ?>
                    </td>

                    <td>
                        <?php echo $order['fullname']; ?>
                    </td>

                    <td>
                        ₱<?php echo number_format($order['total_amount']); ?>
                    </td>

                    <td>

                        <?php

                        $order_id = $order['id'];


                        // JERSEYS

                        $jerseyQuery = mysqli_query(

                            $conn,

                            "SELECT * FROM order_items
                            WHERE order_id = '$order_id'"

                        );

                        while ($item = mysqli_fetch_assoc($jerseyQuery)) {

                            echo "
                            <button class='dropdown-btn' onclick=\"toggleItems('jersey" . $item['id'] . "')\">

                            <b>" . $item['product_name'] . "</b> ▼

                            </button>

                            <div id='jersey" . $item['id'] . "'class='dropdown-content'>

                            Name:
                            " . $item['print_name'] . "

                            <br>

                            Number:
                            " . $item['print_number'] . "
                            
                            <br>
                            
                            Size:
                            " . $item['size'] . "
                            
                            </div>

                            <hr>

                            ";
                        }


                        $accessoryQuery = mysqli_query(

                            $conn,

                            "SELECT * FROM accessory_orders
                            WHERE order_id = '$order_id'"

                        );

                        while ($accessory = mysqli_fetch_assoc($accessoryQuery)) {

                            echo "
                            
                            <button class='dropdown-btn' onclick=\"toggleItems('accessory" . $accessory['id'] . "')\">

                            <b>" . $accessory['accessory_name'] . "</b> ▼

                            </button>

                            <div
                            id='accessory" . $accessory['id'] . "'
                            class='dropdown-content'>

                            Color:
                            " . $accessory['color'] . "

                            </div>

                            <hr>

                            ";
                        }


                        $racketQuery = mysqli_query(

                            $conn,

                            "SELECT * FROM rent_rackets
                            WHERE order_id = '$order_id'"

                        );

                        while ($racket = mysqli_fetch_assoc($racketQuery)) {

                            echo "

                            <button class='dropdown-btn' onclick=\"toggleItems('racket" . $racket['id'] . "')\">

                            <b>" . $racket['racket_name'] . "</b> ▼

                            </button>

                            <div id='racket" . $racket['id'] . "' class='dropdown-content'>

                            Hours:
                            " . $racket['duration'] . "

                            </div>

                            <hr>

                            ";
                        }

                        ?>

                    </td>

                    <td>
                        <?php echo $order['payment_method']; ?>
                    </td>

                    <td>

                        <form action="update_payment.php" method="POST">

                            <input
                                type="hidden"
                                name="order_id"
                                value="<?php echo $order['id']; ?>">

                            <select name="payment_status">

                                <option value="Pending"
                                    <?php
                                    if ($order['payment_status'] == "Pending") {
                                        echo "selected";
                                    }
                                    ?>>
                                    Pending
                                </option>

                                <option value="Paid"
                                    <?php
                                    if ($order['payment_status'] == "Paid") {
                                        echo "selected";
                                    }
                                    ?>>
                                    Paid
                                </option>

                            </select>

                            <button type="submit">
                                Update
                            </button>

                        </form>

                    </td>

                    <td>

                        <a
                            href="delete_order.php?id=<?php echo $order['id']; ?>"
                            onclick="return confirm('Cancel this order?')">

                            <button
                                style="
                background:red;
                color:white;
                border:none;
                padding:8px 12px;
                border-radius:6px;
                cursor:pointer;
            ">

                                Cancel

                            </button>

                        </a>

                    </td>

                </tr>

            <?php } ?>

        </table>

    </div>

    <script>
        function toggleItems(id) {

            let content =
                document.getElementById(id);

            if (content.style.display === "block") {

                content.style.display = "none";

            } else {

                content.style.display = "block";

            }

        }
    </script>

</body>

</html>