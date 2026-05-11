<?php
include "db.php";

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE email='$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {

        // SEND DATA TO JS
        echo "
        <script>
            localStorage.setItem('loggedInUser', JSON.stringify({
                name: '{$user['name']}',
                email: '{$user['email']}'
            }));
            window.location.href = 'home.html';
        </script>
        ";

    } else {
        echo "Wrong password";
    }
} else {
    echo "No user found";
}
?>