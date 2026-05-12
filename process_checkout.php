<?php

session_start();

include "db.php";

if(!isset($_SESSION['user_id'])){

    echo "
    <script>
    alert('Please login first');
    window.location.href='auth.php';
    </script>
    ";

    exit();
}

$user_id = $_SESSION['user_id'];

$payment_method = "COD";

$total_amount = $_POST['total_amount'];

mysqli_query($conn,

"INSERT INTO orders(

user_id,
total_amount,
payment_method,
payment_status

)

VALUES(

'$user_id',
'$total_amount',
'$payment_method',
'Paid'

)"

);

echo "
<script>

localStorage.removeItem('cart');

alert('Payment Successful!');

window.location.href='home.php';

</script>
";

?>