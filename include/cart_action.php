<?php
session_start();
include 'koneksi.php';

// Function to validate and sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if user is logged in
if (!isset($_SESSION['id_user'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$id_user = $_SESSION['id_user'];

// Add to cart action
if (isset($_POST['add_to_cart'])) {
    $id_produk = sanitizeInput($_POST['id_produk']);
    $jumlah = sanitizeInput($_POST['jumlah']);
    
    // Validate input
    if (empty($id_produk) || empty($jumlah) || !is_numeric($jumlah) || $jumlah <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
        exit;
    }
    
    try {
        // Check if product exists
        $check_product = mysqli_query($koneksi, "SELECT * FROM data_produk2 WHERE id_produk = '$id_produk'");
        
        if (mysqli_num_rows($check_product) == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Product not found']);
            exit;
        }
        
        // Check if product already in cart
        $check_cart = mysqli_query($koneksi, "SELECT * FROM cart WHERE id_user = '$id_user' AND id_produk = '$id_produk'");
        
        if (mysqli_num_rows($check_cart) > 0) {
            // Update quantity if product already in cart
            $update = mysqli_query($koneksi, "UPDATE cart SET jumlah = jumlah + $jumlah WHERE id_user = '$id_user' AND id_produk = '$id_produk'");
            
            if ($update) {
                echo json_encode(['status' => 'success', 'message' => 'Product quantity updated in cart']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update cart: ' . mysqli_error($koneksi)]);
            }
        } else {
            // Add new product to cart
            $insert = mysqli_query($koneksi, "INSERT INTO cart (id_user, id_produk, jumlah) VALUES ('$id_user', '$id_produk', '$jumlah')");
            
            if ($insert) {
                echo json_encode(['status' => 'success', 'message' => 'Product added to cart']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to add to cart: ' . mysqli_error($koneksi)]);
            }
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Update cart quantity
if (isset($_POST['update_cart'])) {
    $id_cart = sanitizeInput($_POST['id_cart']);
    $jumlah = sanitizeInput($_POST['jumlah']);
    
    // Validate input
    if (empty($id_cart) || empty($jumlah) || !is_numeric($jumlah)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
        exit;
    }
    
    try {
        if ($jumlah <= 0) {
            // Remove item if quantity is 0 or less
            $delete = mysqli_query($koneksi, "DELETE FROM cart WHERE id_cart = '$id_cart' AND id_user = '$id_user'");
            
            if ($delete) {
                echo json_encode(['status' => 'success', 'message' => 'Item removed from cart']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to remove item: ' . mysqli_error($koneksi)]);
            }
        } else {
            // Update quantity
            $update = mysqli_query($koneksi, "UPDATE cart SET jumlah = '$jumlah' WHERE id_cart = '$id_cart' AND id_user = '$id_user'");
            
            if ($update) {
                echo json_encode(['status' => 'success', 'message' => 'Cart updated']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update cart: ' . mysqli_error($koneksi)]);
            }
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Remove from cart
if (isset($_POST['remove_from_cart'])) {
    $id_cart = sanitizeInput($_POST['id_cart']);
    
    try {
        $delete = mysqli_query($koneksi, "DELETE FROM cart WHERE id_cart = '$id_cart' AND id_user = '$id_user'");
        
        if ($delete) {
            echo json_encode(['status' => 'success', 'message' => 'Item removed from cart']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to remove item: ' . mysqli_error($koneksi)]);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Checkout cart
if (isset($_POST['checkout'])) {
    try {
        // Get cart items
        $cart_query = mysqli_query($koneksi, "SELECT c.*, p.nama_produk, p.harga FROM cart c 
                                             JOIN data_produk2 p ON c.id_produk = p.id_produk 
                                             WHERE c.id_user = '$id_user'");
        
        if (mysqli_num_rows($cart_query) == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cart is empty']);
            exit;
        }
        
        // Create order
        $order_date = date('Y-m-d H:i:s');
        $total_amount = 0;
        
        // Calculate total amount
        while ($cart_item = mysqli_fetch_assoc($cart_query)) {
            $total_amount += $cart_item['harga'] * $cart_item['jumlah'];
        }
        
        // Reset pointer
        mysqli_data_seek($cart_query, 0);
        
        // Insert into orders table
        $insert_order = mysqli_query($koneksi, "INSERT INTO orders (id_user, order_date, total_amount, status) 
                                               VALUES ('$id_user', '$order_date', '$total_amount', 'pending')");
        
        if ($insert_order) {
            $order_id = mysqli_insert_id($koneksi);
            
            // Insert order items
            while ($cart_item = mysqli_fetch_assoc($cart_query)) {
                $id_produk = $cart_item['id_produk'];
                $jumlah = $cart_item['jumlah'];
                $harga = $cart_item['harga'];
                
                $insert_item = mysqli_query($koneksi, "INSERT INTO order_items (order_id, id_produk, quantity, price) 
                                                      VALUES ('$order_id', '$id_produk', '$jumlah', '$harga')");
                
                if (!$insert_item) {
                    // Rollback if item insertion fails
                    mysqli_query($koneksi, "DELETE FROM orders WHERE id = '$order_id'");
                    echo json_encode(['status' => 'error', 'message' => 'Failed to create order items: ' . mysqli_error($koneksi)]);
                    exit;
                }
            }
            
            // Clear cart after successful order
            $clear_cart = mysqli_query($koneksi, "DELETE FROM cart WHERE id_user = '$id_user'");
            
            if ($clear_cart) {
                echo json_encode(['status' => 'success', 'message' => 'Order placed successfully', 'order_id' => $order_id]);
            } else {
                echo json_encode(['status' => 'warning', 'message' => 'Order placed but failed to clear cart: ' . mysqli_error($koneksi), 'order_id' => $order_id]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create order: ' . mysqli_error($koneksi)]);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Default response for invalid requests
echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
?>
