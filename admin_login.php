<?php

session_start();
include "db.php";

$admin_id = $_POST['admin_id'];
$password = $_POST['password'];

$query = mysqli_query(
    $conn,

    "SELECT * FROM admins
WHERE admin_id='$admin_id'
AND password='$password'"
);

if (mysqli_num_rows($query) > 0) {

    $_SESSION['admin'] = $admin_id;

    header("Location: admin.php");
} else {

    echo "
    <script>
        alert('Invalid Admin Login');
        window.location.href='auth.php';
    </script>
    ";
}
