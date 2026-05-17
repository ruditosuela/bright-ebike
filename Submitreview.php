<?php
session_start();
include 'db.php';
 
header('Content-Type: application/json');
 
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to submit a review.']);
    exit;
}
 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}
 
$data = json_decode(file_get_contents('php://input'), true);
 
$product_id  = isset($data['product_id'])  ? (int)$data['product_id']  : 0;
$rating      = isset($data['rating'])      ? (int)$data['rating']      : 0;
$review_text = isset($data['review_text']) ? trim($data['review_text']) : '';
 
if ($product_id <= 0 || $rating < 1 || $rating > 5 || empty($review_text)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields correctly.']);
    exit;
}
 
$username = mysqli_real_escape_string($conn, $_SESSION['username']);
$userResult = mysqli_query($conn, "SELECT user_id FROM customers WHERE username='$username'");
if (!$userResult || mysqli_num_rows($userResult) === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}
$user = mysqli_fetch_assoc($userResult);
$user_id = (int)$user['user_id'];
 
$check = mysqli_query($conn, "SELECT review_id FROM reviews WHERE user_id=$user_id AND product_id=$product_id");
if ($check && mysqli_num_rows($check) > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already reviewed this product.']);
    exit;
}
 
$review_text_escaped = mysqli_real_escape_string($conn, $review_text);
 
$insert = mysqli_query($conn, "
    INSERT INTO reviews (user_id, product_id, rating, review_text)
    VALUES ($user_id, $product_id, $rating, '$review_text_escaped')
");
 
if ($insert) {
    echo json_encode(['success' => true, 'message' => 'Review submitted successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit review. Please try again.']);
}
?>