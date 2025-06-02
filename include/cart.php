<?php
session_start();
include 'include/koneksi.php';

// Check if user is logged in
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// Get cart items
$query = mysqli_query($koneksi, "SELECT c.*, p.nama_produk, p.harga, p.gambar 
                                FROM cart c 
                                JOIN data_produk2 p ON c.id_produk = p.id_produk 
                                WHERE c.id_user = '$id_user'");

// Calculate total
$total = 0;
$cart_items = [];

while ($row = mysqli_fetch_assoc($query)) {
    $cart_items[] = $row;
    $total += $row['harga'] * $row['jumlah'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SnackIn - Shopping Cart</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .cart-item {
            transition: background-color 0.3s ease;
        }
        
        .cart-item:hover {
            background-color: #f8f9fa;
        }
        
        .product-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }
        
        .quantity-input {
            width: 60px;
            text-align: center;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            visibility: hidden;
            opacity: 0;
            transition: visibility 0s, opacity 0.3s linear;
        }
        
        .loading-overlay.show {
            visibility: visible;
            opacity: 1;
        }
        
        .spinner-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .spinner-text {
            margin-top: 10px;
            font-weight: 500;
            color: #333;
        }
        
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        .empty-cart {
            text-align: center;
            padding: 50px 0;
        }
        
        .empty-cart i {
            font-size: 5rem;
            color: #dee2e6;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'include/user_header.php'; ?>
    
    <!-- Main Content -->
    <div class="container py-5">
        <h2 class="mb-4">Shopping Cart</h2>
        
        <?php if (count($cart_items) > 0): ?>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr class="cart-item" data-id="<?php echo $item['id_cart']; ?>">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo !empty($item['gambar']) ? $item['gambar'] : 'assets/images/no-image.jpg'; ?>" class="product-img rounded me-3" alt="<?php echo htmlspecialchars($item['nama_produk']); ?>">
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($item['nama_produk']); ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <button class="btn btn-sm btn-outline-secondary me-2 btn-decrease" data-id="<?php echo $item['id_cart']; ?>">-</button>
                                                <input type="number" class="form-control form-control-sm quantity-input" id="quantity-<?php echo $item['id_cart']; ?>" value="<?php echo $item['jumlah']; ?>" min="1" data-id="<?php echo $item['id_cart']; ?>">
                                                <button class="btn btn-sm btn-outline-secondary ms-2 btn-increase" data-id="<?php echo $item['id_cart']; ?>">+</button>
                                            </div>
                                        </td>
                                        <td class="subtotal">Rp <?php echo number_format($item['harga'] * $item['jumlah'], 0, ',', '.'); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-danger btn-remove" data-id="<?php echo $item['id_cart']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-4">
                        <div class="card" style="width: 18rem;">
                            <div class="card-body">
                                <h5 class="card-title">Order Summary</h5>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span id="cart-total">Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                                </div>
                                <button id="btn-checkout" class="btn btn-primary w-100">Checkout</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h4>Your cart is empty</h4>
                <p>Looks like you haven't added any products to your cart yet.</p>
                <a href="user_ui.php" class="btn btn-primary mt-3">Continue Shopping</a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-container">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
            <div class="spinner-text">Processing...</div>
        </div>
    </div>
    
    <!-- Toast Container -->
    <div class="toast-container"></div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Show loading overlay
            function showLoading() {
                $('#loadingOverlay').addClass('show');
            }
            
            // Hide loading overlay
            function hideLoading() {
                $('#loadingOverlay').removeClass('show');
            }
            
            // Show toast notification
            function showToast(message, type = 'success') {
                const toastId = 'toast-' + Date.now();
                const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
                
                const toast = `
                    <div class="toast ${bgClass} text-white" id="${toastId}" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header">
                            <strong class="me-auto">${type === 'success' ? 'Success' : 'Error'}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            ${message}
                        </div>
                    </div>
                `;
                
                $('.toast-container').append(toast);
                
                const toastElement = new bootstrap.Toast(document.getElementById(toastId), {
                    delay: 3000
                });
                
                toastElement.show();
                
                // Remove toast from DOM after it's hidden
                $(`#${toastId}`).on('hidden.bs.toast', function() {
                    $(this).remove();
                });
            }
            
            // Update cart total
            function updateCartTotal() {
                let total = 0;
                $('.cart-item').each(function() {
                    const price = parseFloat($(this).find('td:nth-child(2)').text().replace('Rp ', '').replace('.', ''));
                    const quantity = parseInt($(this).find('.quantity-input').val());
                    total += price * quantity;
                });
                
                $('#cart-total').text('Rp ' + total.toLocaleString('id-ID'));
            }
            
            // Update cart item
            function updateCartItem(id_cart, jumlah) {
                showLoading();
                
                $.ajax({
                    url: 'include/cart_action.php',
                    type: 'POST',
                    data: {
                        update_cart: true,
                        id_cart: id_cart,
                        jumlah: jumlah
                    },
                    dataType: 'json',
                    success: function(response) {
                        hideLoading();
                        
                        if (response.status === 'success') {
                            if (jumlah <= 0) {
                                $(`.cart-item[data-id="${id_cart}"]`).remove();
                                
                                // Check if cart is empty
                                if ($('.cart-item').length === 0) {
                                    location.reload();
                                }
                            } else {
                                // Update subtotal
                                const price = parseFloat($(`.cart-item[data-id="${id_cart}"]`).find('td:nth-child(2)').text().replace('Rp ', '').replace('.', ''));
                                const subtotal = price * jumlah;
                                $(`.cart-item[data-id="${id_cart}"]`).find('.subtotal').text('Rp ' + subtotal.toLocaleString('id-ID'));
                            }
                            
                            updateCartTotal();
                            showToast(response.message, 'success');
                        } else {
                            showToast(response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        hideLoading();
                        showToast('An error occurred: ' + error, 'error');
                    }
                });
            }
            
            // Increase quantity
            $('.btn-increase').click(function() {
                const id_cart = $(this).data('id');
                const quantityInput = $(`#quantity-${id_cart}`);
                let quantity = parseInt(quantityInput.val()) + 1;
                quantityInput.val(quantity);
                updateCartItem(id_cart, quantity);
            });
            
            // Decrease quantity
            $('.btn-decrease').click(function() {
                const id_cart = $(this).data('id');
                const quantityInput = $(`#quantity-${id_cart}`);
                let quantity = parseInt(quantityInput.val()) - 1;
                if (quantity >= 0) {
                    quantityInput.val(quantity);
                    updateCartItem(id_cart, quantity);
                }
            });
            
            // Update quantity on input change
            $('.quantity-input').change(function() {
                const id_cart = $(this).data('id');
                let quantity = parseInt($(this).val());
                
                if (isNaN(quantity) || quantity < 0) {
                    quantity = 0;
                }
                
                $(this).val(quantity);
                updateCartItem(id_cart, quantity);
            });
            
            // Remove item from cart
            $('.btn-remove').click(function() {
                const id_cart = $(this).data('id');
                
                showLoading();
                
                $.ajax({
                    url: 'include/cart_action.php',
                    type: 'POST',
                    data: {
                        remove_from_cart: true,
                        id_cart: id_cart
                    },
                    dataType: 'json',
                    success: function(response) {
                        hideLoading();
                        
                        if (response.status === 'success') {
                            $(`.cart-item[data-id="${id_cart}"]`).remove();
                            updateCartTotal();
                            showToast(response.message, 'success');
                            
                            // Check if cart is empty
                            if ($('.cart-item').length === 0) {
                                location.reload();
                            }
                        } else {
                            showToast(response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        hideLoading();
                        showToast('An error occurred: ' + error, 'error');
                    }
                });
            });
            
            // Checkout
            $('#btn-checkout').click(function() {
                showLoading();
                
                $.ajax({
                    url: 'include/cart_action.php',
                    type: 'POST',
                    data: {
                        checkout: true
                    },
                    dataType: 'json',
                    success: function(response) {
                        hideLoading();
                        
                        if (response.status === 'success') {
                            showToast(response.message, 'success');
                            
                            // Redirect to history page after successful checkout
                            setTimeout(function() {
                                window.location.href = 'history.php';
                            }, 1500);
                        } else {
                            showToast(response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        hideLoading();
                        showToast('An error occurred: ' + error, 'error');
                    }
                });
            });
        });
    </script>
</body>
</html>
