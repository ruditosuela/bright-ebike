<?php
include 'db.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 1) {
    echo json_encode([]);
    exit;
}

$q = mysqli_real_escape_string($conn, $query);
$sql = "SELECT product_id, product_name, price, image FROM product WHERE product_name LIKE '%$q%' LIMIT 8";
$result = mysqli_query($conn, $sql);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

echo json_encode($products);
?>