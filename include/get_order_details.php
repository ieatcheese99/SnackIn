<?php
session_start();
include 'database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

if (!isset($_GET['order_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Order ID not provided']);
    exit;
}

$orderId = (int)$_GET['order_id'];
$username = $_SESSION['username'];

// Get user ID
$userQuery = mysqli_query($db, "SELECT id FROM users WHERE username = '$username'");
$userData = mysqli_fetch_assoc($userQuery);
$userId = $userData['id'];

// Get order details
$orderQuery = mysqli_query($db, "SELECT * FROM orders WHERE id = '$orderId' AND user_id = '$userId'");

if (mysqli_num_rows($orderQuery) == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Order not found']);
    exit;
}

$order = mysqli_fetch_assoc($orderQuery);

// Get order items
$itemsQuery = mysqli_query($db, "SELECT oi.*, b.nama as nama_produk 
                                FROM order_items oi 
                                JOIN barang b ON oi.product_id = b.id 
                                WHERE oi.order_id = '$orderId'");

$items = [];
while ($item = mysqli_fetch_assoc($itemsQuery)) {
    $items[] = $item;
}

// Format order date
$order['order_date'] = date('d M Y, H:i', strtotime($order['order_date']));

echo json_encode([
    'status' => 'success',
    'order' => $order,
    'items' => $items
]);
?>
