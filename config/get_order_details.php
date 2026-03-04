<?php
session_start();
require_once 'database.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

// Get order ID from request
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid order ID']);
    exit;
}

try {
    // Get order details
    $orderQuery = "SELECT * FROM orders WHERE id = ? AND username = ?";
    $stmt = mysqli_prepare($db, $orderQuery);
    mysqli_stmt_bind_param($stmt, "is", $order_id, $_SESSION['username']);
    mysqli_stmt_execute($stmt);
    $orderResult = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($orderResult);

    if (!$order) {
        echo json_encode(['status' => 'error', 'message' => 'Order not found']);
        exit;
    }

    // Get order items
    $itemsQuery = "SELECT * FROM order_items WHERE order_id = ?";
    $stmt = mysqli_prepare($db, $itemsQuery);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
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

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
