<?php
session_start();
require_once 'database.php';

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

// Get order details
$orderQuery = "SELECT * FROM orders WHERE id = ? AND username = ?";
$stmt = mysqli_prepare($db, $orderQuery);
mysqli_stmt_bind_param($stmt, "is", $orderId, $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Order not found']);
    exit;
}

$order = mysqli_fetch_assoc($result);

// Get order items
$itemsQuery = "SELECT oi.*, oi.nama_produk 
               FROM order_items oi 
               WHERE oi.order_id = ?";
$stmt = mysqli_prepare($db, $itemsQuery);
mysqli_stmt_bind_param($stmt, "i", $orderId);
mysqli_stmt_execute($stmt);
$itemsResult = mysqli_stmt_get_result($stmt);

$items = [];
while ($item = mysqli_fetch_assoc($itemsResult)) {
    $items[] = $item;
}

// Format order date
$order['order_date'] = date('d M Y, H:i', strtotime($order['created_at']));

echo json_encode([
    'status' => 'success',
    'order' => $order,
    'items' => $items
]);
?>
