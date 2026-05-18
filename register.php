<?php

include "db.php";

$name = $_POST['name'];
$email = $_POST['email'];
$address = $_POST['address'];
$contact = $_POST['contact'];
$password = $_POST['password'];

$check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

if(mysqli_num_rows($check) > 0){

    echo "Email already registered!";

} else {

    $sql = "INSERT INTO users(fullname, email, address, contact, password)
    VALUES('$name','$email','$address', '$contact','$password')";

    mysqli_query($conn, $sql);

    header("Location: home.php");
    exit();
}

?>