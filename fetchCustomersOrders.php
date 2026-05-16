<?php
include 'db.php';
 
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
 
$sql = "
    SELECT 
        order_id,
        total_amount,
        status,
        created_at
    FROM orders
    WHERE user_id = $user_id
    ORDER BY created_at DESC
";
 
$result = mysqli_query($conn, $sql);
 
$orders = [];
 
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}
 
header('Content-Type: application/json');
echo json_encode($orders);
?>