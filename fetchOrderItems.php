<?php
include 'db.php';
 
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
 
$sql = "
    SELECT 
        product.product_name,
        order_details.quantity,
        order_details.price
    FROM order_details
    INNER JOIN product ON order_details.product_id = product.product_id
    WHERE order_details.order_id = $order_id
";
 
$result = mysqli_query($conn, $sql);
 
$items = [];
 
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
}
 
header('Content-Type: application/json');
echo json_encode($items);
?>
