<?php
// Start session and include database connection
session_start();
require "config/database.php";

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: history_admin.php");
    exit();
}

$order_id = (int)$_GET['id'];

// Delete order items first (foreign key constraint)
$delete_items_query = "DELETE FROM order_items WHERE order_id = $order_id";
mysqli_query($db, $delete_items_query);

// Delete the order
$delete_order_query = "DELETE FROM orders WHERE id = $order_id";
$result = mysqli_query($db, $delete_order_query);

if ($result) {
    // Set success message
    $_SESSION['admin_message'] = "Pesanan #$order_id berhasil dihapus.";
    $_SESSION['admin_message_type'] = "success";
} else {
    // Set error message
    $_SESSION['admin_message'] = "Gagal menghapus pesanan: " . mysqli_error($db);
    $_SESSION['admin_message_type'] = "danger";
}

// Redirect back to admin history page
header("Location: history_admin.php");
exit();
?>
