<?php
session_start();
require_once '../config/database.php';

// Function to validate and sanitize input
function sanitizeInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['id']) && !isset($_SESSION['username'])) {
    if (isset($_POST['action']) && $_POST['action'] === 'count') {
        echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
        exit;
    }
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get user ID from session
$id_user = isset($_SESSION['id']) ? $_SESSION['id'] : null;

// If id_user is not set, get it from username
if (!$id_user && isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $userQuery = "SELECT id FROM user WHERE username = ?";
    $stmt = mysqli_prepare($db, $userQuery);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $userData = mysqli_fetch_assoc($result);
    $id_user = $userData['id'];
    $_SESSION['id'] = $id_user;
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle different actions
if (isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'add':
            $id_produk = (int) sanitizeInput($_POST['id']);
            $nama = sanitizeInput($_POST['nama']);
            $harga = (int) sanitizeInput($_POST['harga']);
            $gambar = sanitizeInput($_POST['gambar']);
            $quantity = (int) sanitizeInput($_POST['quantity']);

            if ($quantity <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
                exit;
            }

            // Check if product exists and has enough stock
            $productQuery = "SELECT * FROM barang WHERE id = ?";
            $stmt = mysqli_prepare($db, $productQuery);
            mysqli_stmt_bind_param($stmt, "i", $id_produk);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $product = mysqli_fetch_assoc($result);

            if (!$product) {
                echo json_encode(['success' => false, 'message' => 'Product not found']);
                exit;
            }

            if ($product['Stok'] < $quantity) {
                echo json_encode(['success' => false, 'message' => 'Insufficient stock. Available: ' . $product['Stok']]);
                exit;
            }

            // Check if item already in cart
            $found = false;
            foreach ($_SESSION['cart'] as $key => $cart_item) {
                if ($cart_item['id'] == $id_produk) {
                    $newQuantity = $cart_item['jumlah'] + $quantity;

                    if ($product['Stok'] < $newQuantity) {
                        echo json_encode(['success' => false, 'message' => 'Insufficient stock for total quantity']);
                        exit;
                    }

                    $_SESSION['cart'][$key]['jumlah'] = $newQuantity;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                // Add new item to cart
                $_SESSION['cart'][] = [
                    'id' => $id_produk,
                    'nama' => $nama,
                    'harga' => $harga,
                    'gambar' => $gambar,
                    'jumlah' => $quantity
                ];
            }

            echo json_encode(['success' => true, 'message' => 'Product added to cart']);
            break;

        case 'increase':
            $id = (int) $_POST['id'];
            foreach ($_SESSION['cart'] as $key => $item) {
                if ($item['id'] == $id) {
                    // Check stock before increasing
                    $stockQuery = "SELECT Stok FROM barang WHERE id = ?";
                    $stmt = mysqli_prepare($db, $stockQuery);
                    mysqli_stmt_bind_param($stmt, "i", $id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $stockData = mysqli_fetch_assoc($result);

                    if ($stockData && $stockData['Stok'] > $item['jumlah']) {
                        $_SESSION['cart'][$key]['jumlah']++;
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Insufficient stock']);
                    }
                    exit;
                }
            }
            echo json_encode(['success' => false, 'error' => 'Item not found']);
            break;

        case 'decrease':
            $id = (int) $_POST['id'];
            foreach ($_SESSION['cart'] as $key => $item) {
                if ($item['id'] == $id) {
                    if ($item['jumlah'] > 1) {
                        $_SESSION['cart'][$key]['jumlah']--;
                        echo json_encode(['success' => true]);
                    } else {
                        unset($_SESSION['cart'][$key]);
                        $_SESSION['cart'] = array_values($_SESSION['cart']);
                        echo json_encode(['success' => true]);
                    }
                    exit;
                }
            }
            echo json_encode(['success' => false, 'error' => 'Item not found']);
            break;

        case 'remove':
            $id = (int) $_POST['id'];
            foreach ($_SESSION['cart'] as $key => $item) {
                if ($item['id'] == $id) {
                    unset($_SESSION['cart'][$key]);
                    $_SESSION['cart'] = array_values($_SESSION['cart']);
                    echo json_encode(['success' => true]);
                    exit;
                }
            }
            echo json_encode(['success' => false, 'error' => 'Item not found']);
            break;

        case 'count':
            echo count($_SESSION['cart']);
            break;

        case 'checkout':
            if (empty($_SESSION['cart'])) {
                echo json_encode(['success' => false, 'error' => 'Cart is empty']);
                exit;
            }

            $nama = sanitizeInput($_POST['nama']);
            $alamat = sanitizeInput($_POST['alamat']);
            $metode_pembayaran = sanitizeInput($_POST['metode_pembayaran']);

            // Calculate total
            $total = 0;
            foreach ($_SESSION['cart'] as $item) {
                $total += $item['harga'] * $item['jumlah'];
            }

            // Add admin fee (5%)
            $biaya_admin = $total * 0.05;
            $total_with_admin = $total + $biaya_admin;

            // Start transaction
            mysqli_begin_transaction($db);

            try {
                // Create order
                $orderQuery = "INSERT INTO orders (nama, alamat, metode_pembayaran, total_harga, status, username, created_at) VALUES (?, ?, ?, ?, 'pending', ?, NOW())";
                $stmt = mysqli_prepare($db, $orderQuery);
                mysqli_stmt_bind_param($stmt, "sssds", $nama, $alamat, $metode_pembayaran, $total_with_admin, $_SESSION['username']);
                mysqli_stmt_execute($stmt);
                $order_id = mysqli_insert_id($db);

                // Add order items and update stock
                foreach ($_SESSION['cart'] as $item) {
                    // Insert order item
                    $orderItemQuery = "INSERT INTO order_items (order_id, produk_id, nama_produk, harga, jumlah, subtotal) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = mysqli_prepare($db, $orderItemQuery);
                    $subtotal = $item['harga'] * $item['jumlah'];
                    mysqli_stmt_bind_param($stmt, "iisiii", $order_id, $item['id'], $item['nama'], $item['harga'], $item['jumlah'], $subtotal);
                    mysqli_stmt_execute($stmt);

                    // Update stock
                    $updateStockQuery = "UPDATE barang SET Stok = Stok - ? WHERE id = ?";
                    $stmt = mysqli_prepare($db, $updateStockQuery);
                    mysqli_stmt_bind_param($stmt, "ii", $item['jumlah'], $item['id']);
                    mysqli_stmt_execute($stmt);
                }

                // Clear cart
                $_SESSION['cart'] = [];

                // Commit transaction
                mysqli_commit($db);

                echo json_encode(['success' => true, 'message' => 'Order placed successfully', 'order_id' => $order_id]);

            } catch (Exception $e) {
                // Rollback transaction
                mysqli_rollback($db);
                echo json_encode(['success' => false, 'error' => 'Failed to place order: ' . $e->getMessage()]);
            }
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No action specified']);
}
?>