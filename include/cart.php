<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['id']) && !isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart = $_SESSION['cart'];
$totalHarga = 0;

// Calculate total
foreach ($cart as $item) {
    $totalHarga += $item['harga'] * $item['jumlah'];
}

// Calculate admin fee (5%)
$biaya_admin = $totalHarga * 0.05;
$total_with_admin = $totalHarga + $biaya_admin;

$page_title = "Keranjang Belanja";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Snack In</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #00227c;
            --secondary-color: #f69e22;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        /* Cart Page Styles */
        .cart-section {
            padding: 40px 0;
            min-height: calc(100vh - 300px);
        }

        .cart-title {
            color: #00227c;
            text-align: center;
            margin-bottom: 15px;
            font-weight: 700;
            font-size: 32px;
        }

        .cart-subtitle {
            color: #f69e22;
            font-size: 18px;
            text-align: center;
            margin-bottom: 40px;
        }

        .cart-empty {
            text-align: center;
            padding: 60px 40px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .cart-empty i {
            font-size: 80px;
            color: #f69e22;
            margin-bottom: 20px;
        }

        .cart-empty h3 {
            font-size: 24px;
            margin-bottom: 15px;
            color: #00227c;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .cart-table th {
            background-color: #00227c;
            color: white;
            padding: 18px 15px;
            text-align: center;
            font-weight: 600;
            font-size: 14px;
        }

        .cart-table td {
            padding: 20px 15px;
            text-align: center;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }

        .cart-table tr:last-child td {
            border-bottom: none;
        }

        .cart-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .cart-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .cart-product-name {
            font-weight: 600;
            color: #00227c;
            font-size: 16px;
        }

        .cart-price {
            font-weight: 600;
            color: #f69e22;
            font-size: 16px;
        }

        .cart-quantity {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .quantity-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            color: #666;
        }

        .quantity-btn:hover {
            background-color: #00227c;
            color: white;
            border-color: #00227c;
            transform: scale(1.1);
        }

        .quantity-value {
            font-weight: 600;
            width: 40px;
            text-align: center;
            font-size: 16px;
            color: #333;
        }

        .cart-total {
            font-weight: 700;
            color: #00227c;
            font-size: 16px;
        }

        .cart-remove {
            background-color: #ff6b6b;
            color: white;
            border: none;
            padding: 10px 14px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .cart-remove:hover {
            background-color: #e74c3c;
            transform: scale(1.05);
        }

        .cart-summary {
            background-color: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 100px;
        }

        .summary-title {
            font-size: 20px;
            font-weight: 700;
            color: #00227c;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            align-items: center;
        }

        .summary-label {
            font-weight: 500;
            color: #666;
            font-size: 15px;
        }

        .summary-value {
            font-weight: 600;
            color: #333;
            font-size: 15px;
        }

        .summary-total {
            font-size: 22px;
            font-weight: 700;
            color: #f69e22;
            border-top: 2px solid #f0f0f0;
            padding-top: 15px;
            margin-top: 15px;
        }

        .btn-checkout {
            background-color: #f69e22;
            color: white;
            border: none;
            padding: 15px 25px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .btn-checkout:hover {
            background-color: #e08a10;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(246, 158, 34, 0.3);
        }

        .btn-continue {
            background: #00227c;
            color: white;
            padding: 12px 25px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
            margin-top: 20px;
            width: 100%;
            text-align: center;
        }

        .btn-continue:hover {
            background: #001a5e;
            color: white;
            transform: translateY(-2px);
        }

        .btn-orange {
            background-color: #f69e22;
        }

        .btn-orange:hover {
            background-color: #e08a10;
        }

        /* Success Popup */
        .success-popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 3000;
            backdrop-filter: blur(5px);
        }

        .success-popup-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 16px;
            padding: 40px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: successSlideIn 0.5s ease-out;
        }

        @keyframes successSlideIn {
            from {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.8);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }

        .success-checkmark {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
            display: flex;
            align-items: center;
            justify-content: center;
            animation: checkmarkPulse 0.6s ease-out;
        }

        @keyframes checkmarkPulse {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }

        .success-checkmark i {
            color: white;
            font-size: 36px;
        }

        .success-title {
            font-size: 24px;
            font-weight: 700;
            color: #00227c;
            margin-bottom: 15px;
        }

        .success-message {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .success-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #5a6268;
            color: white;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            color: white;
        }

        /* Modal Styles */
        .modal-content {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background-color: #00227c;
            color: white;
            border-bottom: none;
            padding: 20px 25px;
        }

        .modal-title {
            font-weight: 600;
        }

        .modal-body {
            padding: 30px 25px;
        }

        .form-label {
            font-weight: 600;
            color: #00227c;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            padding: 12px 15px;
            border-radius: 10px;
            border: 2px solid #e9ecef;
            margin-bottom: 20px;
            font-size: 15px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #00227c;
            box-shadow: 0 0 0 0.25rem rgba(0, 34, 124, 0.25);
        }

        .payment-info {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
            border: 1px solid #e9ecef;
        }

        .payment-info-title {
            font-weight: 600;
            color: #00227c;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .payment-qr {
            max-width: 200px;
            margin: 0 auto 15px;
            display: block;
            border-radius: 10px;
        }

        .payment-account {
            background-color: white;
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
            border: 1px solid #e9ecef;
        }

        .payment-account-number {
            font-weight: 700;
            color: #00227c;
            font-size: 18px;
        }

        .payment-note {
            font-size: 14px;
            color: #666;
            margin-top: 15px;
            font-style: italic;
            line-height: 1.5;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .main-nav {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .cart-table {
                font-size: 14px;
            }
            
            .cart-table th,
            .cart-table td {
                padding: 12px 8px;
            }

            .cart-img {
                width: 60px;
                height: 60px;
            }

            .cart-quantity {
                gap: 8px;
            }

            .quantity-btn {
                width: 28px;
                height: 28px;
            }

            .quantity-value {
                width: 30px;
                font-size: 14px;
            }

            .cart-summary {
                margin-top: 20px;
                position: static;
            }
        }

        @media (max-width: 576px) {
            .cart-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .cart-section {
                padding: 20px 0;
            }

            .cart-title {
                font-size: 24px;
            }

            .cart-subtitle {
                font-size: 16px;
            }
        }

        /* Notification */
        #cart-notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-20px);
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s, transform 0.3s;
            min-width: 300px;
            max-width: 80%;
            text-align: center;
            font-size: 16px;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <?php 
    // Set cart count for the header
    $cartCount = count($cart);
    // Include the user header
    include_once '../user_header.php'; 
    ?>

    <!-- Success Popup -->
    <div class="success-popup" id="success-popup">
        <div class="success-popup-content">
            <div class="success-checkmark">
                <i class="fas fa-check"></i>
            </div>
            <h2 class="success-title">Pesanan Berhasil!</h2>
            <p class="success-message">Pesanan Anda telah berhasil diproses. Anda akan menerima konfirmasi segera.</p>
            <div class="success-actions">
                <button class="btn-secondary" onclick="hideSuccessPopup()">Lanjut Belanja</button>
                <a href="../history.php" class="btn-primary">Lihat Pesanan</a>
            </div>
        </div>
    </div>

    <!-- Cart Section -->
    <section class="cart-section">
        <div class="container">
            <h1 class="cart-title">Keranjang Belanja</h1>
            <p class="cart-subtitle">Review dan sesuaikan pesanan Anda sebelum checkout</p>

            <?php if (empty($cart)): ?>
                <div class="cart-empty">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Keranjang Anda Kosong</h3>
                    <p>Sepertinya Anda belum menambahkan produk apapun ke keranjang.</p>
                    <a href="../user_ui.php" class="btn-continue">
                        <i class="fas fa-shopping-bag me-2"></i>Belanja Sekarang
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="table-responsive">
                            <table class="cart-table">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Nama</th>
                                        <th>Harga</th>
                                        <th>Jumlah</th>
                                        <th>Total</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart as $index => $item): ?>
                                        <tr>
                                            <td>
                                                <img src="../<?php echo htmlspecialchars($item['gambar']); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['nama']); ?>" 
                                                     class="cart-img"
                                                     onerror="this.src='../assets/images/no-image.jpg'">
                                            </td>
                                            <td class="cart-product-name"><?php echo htmlspecialchars($item['nama']); ?></td>
                                            <td class="cart-price">Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                                            <td>
                                                <div class="cart-quantity">
                                                    <button class="quantity-btn decrease" data-id="<?php echo $item['id']; ?>">-</button>
                                                    <span class="quantity-value"><?php echo $item['jumlah']; ?></span>
                                                    <button class="quantity-btn increase" data-id="<?php echo $item['id']; ?>">+</button>
                                                </div>
                                            </td>
                                            <td class="cart-total">Rp <?php echo number_format($item['harga'] * $item['jumlah'], 0, ',', '.'); ?></td>
                                            <td>
                                                <button class="cart-remove remove-item" data-id="<?php echo $item['id']; ?>">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="cart-summary">
                            <h3 class="summary-title">Ringkasan Pesanan</h3>
                            <div class="summary-row">
                                <span class="summary-label">Subtotal</span>
                                <span class="summary-value">Rp <?php echo number_format($totalHarga, 0, ',', '.'); ?></span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Pengiriman</span>
                                <span class="summary-value">Gratis</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Biaya Admin</span>
                                <span class="summary-value">Rp <?php echo number_format($biaya_admin, 0, ',', '.'); ?></span>
                            </div>
                            <div class="summary-row summary-total">
                                <span class="summary-label">Total</span>
                                <span class="summary-value">Rp <?php echo number_format($total_with_admin, 0, ',', '.'); ?></span>
                            </div>

                            <button class="btn-checkout" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                                Checkout Sekarang
                            </button>
                        </div>

                        <a href="../user_ui.php" class="btn-continue">
                            <i class="fas fa-arrow-left me-2"></i> Lanjutkan Belanja
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modal Checkout -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkoutModalLabel">Form Checkout</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="checkout-form">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama" id="nama" required placeholder="Masukkan nama lengkap Anda" value="<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat Pengiriman</label>
                            <textarea class="form-control" name="alamat" id="alamat" rows="3" required placeholder="Masukkan alamat lengkap pengiriman"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="metode_pembayaran" class="form-label">Metode Pembayaran</label>
                            <select name="metode_pembayaran" id="metode_pembayaran" class="form-select" required>
                                <option value="">-- Pilih Metode Pembayaran --</option>
                                <option value="Transfer Bank">Transfer Bank</option>
                                <option value="Qris">QRIS</option>
                                <option value="Cash">Cash on Delivery (COD)</option>
                            </select>
                        </div>

                        <div id="info-pembayaran" class="payment-info" style="display: none;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-orange">Bayar Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Notification Element -->
    <div id="cart-notification"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            // Show/Hide success popup
            function showSuccessPopup() {
                $('#success-popup').fadeIn(300);
                $('body').css('overflow', 'hidden');
            }
            
            window.hideSuccessPopup = function() {
                $('#success-popup').fadeOut(200);
                $('body').css('overflow', '');
            }
            
            // Show notification
            function showNotification(message) {
                if (!document.getElementById('cart-notification')) {
                    const notification = document.createElement('div');
                    notification.id = 'cart-notification';
                    document.body.appendChild(notification);
                }

                const notification = document.getElementById('cart-notification');
                notification.textContent = message;
                notification.style.opacity = '1';
                notification.style.transform = 'translateX(-50%) translateY(0)';

                setTimeout(() => {
                    notification.style.opacity = '0';
                    notification.style.transform = 'translateX(-50%) translateY(-20px)';
                }, 3000);
            }
            
            // Update cart item
            function updateCartItem(action, id) {
                $.ajax({
                    url: 'cart_action.php',
                    type: 'POST',
                    data: {
                        action: action,
                        id: id
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            showNotification(response.error || response.message);
                        }
                    },
                    error: function() {
                        showNotification('Terjadi kesalahan');
                    }
                });
            }
            
            // Increase quantity
            $('.increase').click(function() {
                const id = $(this).data('id');
                updateCartItem('increase', id);
            });
            
            // Decrease quantity
            $('.decrease').click(function() {
                const id = $(this).data('id');
                updateCartItem('decrease', id);
            });
            
            // Remove item
            $('.remove-item').click(function() {
                const id = $(this).data('id');
                
                if (confirm('Apakah Anda yakin ingin menghapus item ini?')) {
                    updateCartItem('remove', id);
                }
            });
            
            // Payment method change
            $("#metode_pembayaran").on("change", function() {
                let metode = $(this).val();
                let infoContainer = $("#info-pembayaran");

                if (metode === "Qris") {
                    infoContainer.html(`
                        <h6 class="payment-info-title"><i class="fas fa-qrcode me-2"></i>Pembayaran via QRIS</h6>
                        <div style="text-align: center; margin-bottom: 15px;">
                            <img src="../assets/img/qris-code.png" alt="QRIS Code" class="payment-qr">
                        </div>
                        <p class="payment-note">Scan QR code di atas menggunakan aplikasi e-wallet atau mobile banking Anda. Setelah pembayaran berhasil, klik "Bayar Sekarang".</p>
                    `).show();
                } else if (metode === "Transfer Bank") {
                    infoContainer.html(`
                        <h6 class="payment-info-title"><i class="fas fa-university me-2"></i>Transfer Bank</h6>
                        <div class="payment-account">
                            <p style="margin: 0; font-weight: 600;">Bank BCA</p>
                            <p class="payment-account-number" style="margin: 5px 0;">1234567890</p>
                            <p style="margin: 0;">a.n. SNACK IN</p>
                        </div>
                        <p class="payment-note">Silakan transfer ke rekening di atas. Setelah transfer berhasil, klik "Bayar Sekarang".</p>
                    `).show();
                } else if (metode === "Cash") {
                    infoContainer.html(`
                        <h6 class="payment-info-title"><i class="fas fa-money-bill-wave me-2"></i>Cash on Delivery (COD)</h6>
                        <p class="payment-note">Anda akan membayar saat pesanan tiba. Pastikan alamat pengiriman sudah benar.</p>
                    `).show();
                } else {
                    infoContainer.hide();
                }
            });
            
            // Checkout form submission
            $("#checkout-form").submit(function(event) {
                event.preventDefault();
                
                showNotification("Memproses pesanan Anda...");
                
                $.ajax({
                    url: 'cart_action.php',
                    type: 'POST',
                    data: $(this).serialize() + "&action=checkout",
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#checkoutModal').modal('hide');
                            showSuccessPopup();
                        } else {
                            showNotification(response.error || response.message);
                        }
                    },
                    error: function() {
                        showNotification('Terjadi kesalahan saat memproses pesanan');
                    }
                });
            });
        });
    </script>
</body>
</html>
