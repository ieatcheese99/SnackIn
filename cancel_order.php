<?php
// Start session and include database connection
session_start();
require "config/database.php";

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = (int)$_POST['order_id'];
    $username = $_SESSION['username'];
    
    // Verify that the order belongs to the current user
    $check_query = "SELECT id FROM orders WHERE id = $order_id AND nama = '$username'";
    $check_result = mysqli_query($db, $check_query);
    
    if ($check_result && mysqli_num_rows($check_result) > 0) {
        // Update order status to cancelled
        $update_query = "UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = $order_id";
        $result = mysqli_query($db, $update_query);
        
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => "Gagal membatalkan pesanan: " . mysqli_error($db)]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => "Pesanan tidak ditemukan atau bukan milik Anda"]);
    }
} else {
    echo json_encode(['success' => false, 'error' => "Data tidak lengkap"]);
}
?>
