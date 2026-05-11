<?php
include "db.php";

$name = $_POST['name'];
$email = $_POST['email'];
$contact = $_POST['contact'];
$password = $_POST['password'];

// hash password (IMPORTANT)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (name, email, contact, password)
        VALUES ('$name', '$email', '$contact', '$hashed_password')";

if ($conn->query($sql) === TRUE) {
    echo "Registered successfully!";
    header("Location: auth.html");
} else {
    echo "Error: " . $conn->error;
}
?>