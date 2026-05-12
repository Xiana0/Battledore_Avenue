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
                        <?php echo $row['booking_date']; ?>
                    </td>

                    <td>
                        <?php echo $row['booking_time']; ?>
                    </td>

                    <td>

                        <span class="status">

                            <?php echo $row['status']; ?>

                        </span>

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

                </tr>

            <?php } ?>

        </table>



        <!-- ORDERS -->

        <h2 style="margin-top:50px;">
            Orders
        </h2>

        <table>

            <tr>

                <th>Order ID</th>
                <th>Customer</th>
                <th>Total</th>
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

</body>

</html>