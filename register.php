<?php

include "db.php";

$name = $_POST['name'];
$email = $_POST['email'];
$contact = $_POST['contact'];
$password = $_POST['password'];
$address = $_POST['address'];

$check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

if(mysqli_num_rows($check) > 0){

    echo "Email already registered!";

} else {

    $sql = "INSERT INTO users(fullname,email,contact,password)
    VALUES('$name','$email','$contact','$password', '$address')";

    mysqli_query($conn, $sql);

    header("Location: home.php");
    exit();
}

?>