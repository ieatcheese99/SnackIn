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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = mysqli_real_escape_string($db, $_POST['status']);
    
    // Valid status values
    $valid_statuses = ['processing', 'delivered'];
    
    if (in_array($status, $valid_statuses)) {
        // Update order status
        $update_query = "UPDATE orders SET status = '$status' WHERE id = $order_id";
        $result = mysqli_query($db, $update_query);
        
        if ($result) {
            // Redirect back to the order detail or list page
            if (isset($_SERVER['HTTP_REFERER'])) {
                header("Location: " . $_SERVER['HTTP_REFERER']);
            } else {
                header("Location: history_admin.php");
            }
            exit();
        } else {
            $error = "Gagal mengupdate status pesanan: " . mysqli_error($db);
        }
    } else {
        $error = "Status tidak valid";
    }
} else {
    $error = "Data tidak lengkap";
}

// If there's an error, display it
if (isset($error)) {
    echo "<!DOCTYPE html>
    <html lang='id'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Error</title>
        <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css'>
    </head>
    <body>
        <div class='container py-5'>
            <div class='alert alert-danger'>
                <h4>Error</h4>
                <p>$error</p>
                <a href='history_admin.php' class='btn btn-primary mt-3'>Kembali</a>
            </div>
        </div>
    </body>
    </html>";
}
?>
