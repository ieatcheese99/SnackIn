<?php
session_start();
include 'database.php';

// Function to validate and sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$username = $_SESSION['username'];

// Get user ID
$userQuery = mysqli_query($db, "SELECT id FROM users WHERE username = '$username'");
$userData = mysqli_fetch_assoc($userQuery);
$userId = $userData['id'];

// Handle different actions
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'add':
            $productId = sanitizeInput($_POST['id']);
            $quantity = (int)sanitizeInput($_POST['quantity']);
            
            if ($quantity <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
                exit;
            }
            
            // Check product stock
            $stockQuery = mysqli_query($db, "SELECT Stok FROM barang WHERE id = '$productId'");
            $stockData = mysqli_fetch_assoc($stockQuery);
            
            if (!$stockData) {
                echo json_encode(['success' => false, 'message' => 'Product not found']);
                exit;
            }
            
            if ($stockData['Stok'] < $quantity) {
                echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
                exit;
            }
            
            // Check if item already in cart
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            $found = false;
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['id'] == $productId) {
                    $item['quantity'] += $quantity;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $_SESSION['cart'][] = [
                    'id' => $productId,
                    'nama' => $_POST['nama'],
                    'harga' => $_POST['harga'],
                    'gambar' => $_POST['gambar'],
                    'quantity' => $quantity
                ];
            }
            
            // Update stock in database
            $newStock = $stockData['Stok'] - $quantity;
            mysqli_query($db, "UPDATE barang SET Stok = '$newStock' WHERE id = '$productId'");
            
            echo json_encode(['success' => true, 'message' => 'Product added to cart']);
            break;
            
        case 'count':
            $count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
            echo $count;
            break;
            
        case 'remove':
            $productId = sanitizeInput($_POST['id']);
            
            if (isset($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $key => $item) {
                    if ($item['id'] == $productId) {
                        // Restore stock
                        $quantity = $item['quantity'];
                        mysqli_query($db, "UPDATE barang SET Stok = Stok + '$quantity' WHERE id = '$productId'");
                        
                        unset($_SESSION['cart'][$key]);
                        $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
                        break;
                    }
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
            break;
            
        case 'update':
            $productId = sanitizeInput($_POST['id']);
            $newQuantity = (int)sanitizeInput($_POST['quantity']);
            
            if (isset($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as &$item) {
                    if ($item['id'] == $productId) {
                        $oldQuantity = $item['quantity'];
                        $difference = $newQuantity - $oldQuantity;
                        
                        // Check stock availability
                        $stockQuery = mysqli_query($db, "SELECT Stok FROM barang WHERE id = '$productId'");
                        $stockData = mysqli_fetch_assoc($stockQuery);
                        
                        if ($difference > 0 && $stockData['Stok'] < $difference) {
                            echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
                            exit;
                        }
                        
                        // Update cart and stock
                        $item['quantity'] = $newQuantity;
                        mysqli_query($db, "UPDATE barang SET Stok = Stok - '$difference' WHERE id = '$productId'");
                        
                        if ($newQuantity <= 0) {
                            unset($_SESSION['cart'][array_search($item, $_SESSION['cart'])]);
                            $_SESSION['cart'] = array_values($_SESSION['cart']);
                        }
                        
                        break;
                    }
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Cart updated']);
            break;
            
        case 'checkout':
            if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
                echo json_encode(['success' => false, 'message' => 'Cart is empty']);
                exit;
            }
            
            // Calculate total
            $total = 0;
            foreach ($_SESSION['cart'] as $item) {
                $total += $item['harga'] * $item['quantity'];
            }
            
            // Create order record (you can expand this based on your order table structure)
            $orderDate = date('Y-m-d H:i:s');
            $orderQuery = "INSERT INTO orders (user_id, total_amount, order_date, status) VALUES ('$userId', '$total', '$orderDate', 'pending')";
            
            if (mysqli_query($db, $orderQuery)) {
                $orderId = mysqli_insert_id($db);
                
                // Insert order items
                foreach ($_SESSION['cart'] as $item) {
                    $itemQuery = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ('$orderId', '{$item['id']}', '{$item['quantity']}', '{$item['harga']}')";
                    mysqli_query($db, $itemQuery);
                }
                
                // Clear cart
                unset($_SESSION['cart']);
                
                echo json_encode(['success' => true, 'message' => 'Order placed successfully', 'order_id' => $orderId]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to place order']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No action specified']);
}
?>
