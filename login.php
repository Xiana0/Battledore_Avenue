<?php

session_start();
include "db.php";

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM users 
WHERE email='$email' 
AND password='$password'";

$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) > 0){

    if(mysqli_num_rows($result) > 0){

    $user = mysqli_fetch_assoc($result);

    $_SESSION['user_id'] = $user['id'];

    $_SESSION['user_name'] = $user['fullname'];

    header("Location: home.php");
    exit();

}

} else {

    echo "Invalid Login";

}

?>