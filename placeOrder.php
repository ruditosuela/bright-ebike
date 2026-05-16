<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    die("Please login first.");
}

$username = $_SESSION['username'];

$userQuery = mysqli_query($conn,
    "SELECT * FROM customers WHERE username='$username'"
);

$user = mysqli_fetch_assoc($userQuery);

$user_id = $user['user_id'];

// GET JSON DATA
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['cart'])) {
    die("Cart is empty.");
}

$cart = $data['cart'];
$total = $data['total'];
$payment = $data['payment'];

/* =========================
   START TRANSACTION
========================= */
mysqli_begin_transaction($conn);

try {

    // INSERT ORDER
    $orderSql = "
        INSERT INTO orders (user_id, total_amount, payment_method, status)
        VALUES ('$user_id', '$total', '$payment', 'Pending')
    ";

    mysqli_query($conn, $orderSql);

    $order_id = mysqli_insert_id($conn);
    
foreach ($cart as $item) {

    $product_id = intval($item['product_id']); // use product_id directly
    $quantity = intval($item['quantity']);
    $price = floatval($item['price']);

    if (!$product_id) continue;

    mysqli_query($conn,
        "INSERT INTO order_details
        (order_id, product_id, quantity, price)
        VALUES
        ('$order_id', '$product_id', '$quantity', '$price')"
    );

    // UPDATE STOCKS
    mysqli_query($conn,
        "UPDATE stocks
        SET quantity = quantity - $quantity
        WHERE product_id='$product_id'"
    );
}
    mysqli_commit($conn);

    echo "Order placed successfully!";

} catch (Exception $e) {

    mysqli_rollback($conn);
    echo "Error placing order.";
}
?>
