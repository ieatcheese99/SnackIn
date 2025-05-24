<?php
session_start();
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$totalHarga = array_sum(array_map(function ($item) {
    return $item['harga'] * $item['jumlah'];
}, $cart));
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viwewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Snack In</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        ul {
            list-style: none;
        }

        img {
            max-width: 100%;
            height: auto;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #00227c;
            color: white;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-align: center;
        }

        .btn:hover {
            background-color: #001a5e;
            transform: translateY(-2px);
        }

        .btn-orange {
            background-color: #f69e22;
        }

        .btn-orange:hover {
            background-color: #e08a10;
        }

        
        /* Main Header */
        .main-header {
            background-color: #00227c;
            color: white;
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .main-header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 700;
        }

        .logo img {
            height: 40px;
            width: auto;
        }

        .main-nav ul {
            display: flex;
            gap: 30px;
        }

        .main-nav a {
            font-weight: 500;
            transition: color 0.3s ease;
            position: relative;
        }

        .main-nav a:hover {
            color: rgba(255, 255, 255, 0.8);
        }

        .main-nav a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: white;
            transition: width 0.3s ease;
        }

        .main-nav a:hover::after {
            width: 100%;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .action-icon {
            position: relative;
            font-size: 18px;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .action-icon:hover {
            color: rgba(255, 255, 255, 0.8);
        }

        .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: white;
            color: #00227c;
            font-size: 10px;
            font-weight: 600;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-icon {
            color: #ff6b6b;
        }

        .mobile-menu-toggle {
            display: none;
            font-size: 24px;
            cursor: pointer;
        }

        /* Cart Page Styles */
        .cart-section {
            padding: 40px 0;
            min-height: calc(100vh - 300px);
        }

        .cart-title {
            color: #00227c;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 32px;
        }

        .cart-subtitle {
            color: #f69e22;
            font-size: 18px;
            text-align: center;
            margin-bottom: 30px;
        }

        .cart-empty {
            text-align: center;
            padding: 40px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .cart-empty i {
            font-size: 60px;
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
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .cart-table th {
            background-color: #00227c;
            color: white;
            padding: 15px;
            text-align: center;
            font-weight: 600;
        }

        .cart-table td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .cart-table tr:last-child td {
            border-bottom: none;
        }

        .cart-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .cart-product-name {
            font-weight: 600;
            color: #00227c;
        }

        .cart-price {
            font-weight: 500;
            color: #f69e22;
        }

        .cart-quantity {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .quantity-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            background-color: #00227c;
            color: white;
            border-color: #00227c;
        }

        .quantity-value {
            font-weight: 600;
            width: 30px;
            text-align: center;
        }

        .cart-total {
            font-weight: 600;
            color: #00227c;
        }

        .cart-remove {
            background-color: #ff6b6b;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .cart-remove:hover {
            background-color: #e74c3c;
            transform: scale(1.05);
        }

        .cart-summary {
            margin-top: 30px;
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .summary-title {
            font-size: 20px;
            font-weight: 600;
            color: #00227c;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .summary-label {
            font-weight: 500;
            color: #666;
        }

        .summary-value {
            font-weight: 600;
            color: #00227c;
        }

        .summary-total {
            font-size: 20px;
            font-weight: 700;
            color: #f69e22;
        }

        .cart-actions {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .cart-actions {
                flex-direction: column;
            }
        }

        /* Modal Styles */
        .modal-content {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background-color: #00227c;
            color: white;
            border-bottom: none;
            padding: 20px;
        }

        .modal-title {
            font-weight: 600;
        }

        .modal-body {
            padding: 30px;
        }

        .form-label {
            font-weight: 500;
            color: #00227c;
        }

        .form-control, .form-select {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #00227c;
            box-shadow: 0 0 0 0.25rem rgba(0, 34, 124, 0.25);
        }

        .payment-info {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }

        .payment-info-title {
            font-weight: 600;
            color: #00227c;
            margin-bottom: 10px;
        }

        .payment-qr {
            max-width: 200px;
            margin: 0 auto;
            display: block;
        }

        .payment-account {
            background-color: white;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
            border: 1px solid #eee;
        }

        .payment-account-number {
            font-weight: 600;
            color: #00227c;
        }

        .payment-note {
            font-size: 14px;
            color: #666;
            margin-top: 15px;
            font-style: italic;
        }

        /* Footer */
        .footer {
            background-color: #222;
            color: white;
            padding-top: 60px;
            margin-top: 60px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-column h3 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #444;
        }

        .footer-column ul li {
            margin-bottom: 10px;
        }

        .footer-column a {
            transition: color 0.3s ease;
        }

        .footer-column a:hover {
            color: #f69e22;
        }

        .footer-contact li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 15px;
        }

        .footer-contact i {
            color: #888;
            margin-top: 5px;
        }

        .footer-newsletter p {
            margin-bottom: 20px;
            color: #ccc;
        }

        .newsletter-form {
            display: flex;
            gap: 10px;
        }

        .newsletter-form input {
            flex: 1;
            padding: 10px;
            border: none;
            background-color: #333;
            color: white;
            border-radius: 5px;
        }

        .footer-social {
            margin-top: 20px;
        }

        .footer-social h4 {
            font-size: 16px;
            margin-bottom: 15px;
        }

        .social-icons {
            display: flex;
            gap: 15px;
        }

        .social-icons a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: #333;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .social-icons a:hover {
            background-color: #f69e22;
            transform: translateY(-3px);
        }

        .footer-bottom {
            border-top: 1px solid #444;
            padding: 20px 0;
            text-align: center;
        }

        .footer-bottom p {
            color: #888;
            font-size: 14px;
        }

        .footer-bottom a {
            color: #f69e22;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .main-nav {
                display: none;
            }

            .mobile-menu-toggle {
                display: block;
            }

            

            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .cart-table {
                display: block;
                overflow-x: auto;
            }

            .cart-img {
                width: 60px;
                height: 60px;
            }

            .footer-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .newsletter-form {
                flex-direction: column;
                gap: 10px;
            }

            .newsletter-form .btn {
                width: 100%;
            }
        }
    
        @media (max-width: 576px) {
            .cart-actions {
                flex-direction: column;
            }

            .cart-actions .btn {
                width: 100%;
            }
        }

        /* Notification */
        #cart-notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-20px);
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s, transform 0.3s;
            min-width: 300px;
            max-width: 80%;
            text-align: center;
            font-size: 18px;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <!-- Main Header -->
    <header class="main-header">
        <div class="container">
            <a href="../user_ui.php" class="logo">
                <img src="../assets/img/Logo Bisnis Bengkel Otomotif (3).png" alt="Snack In Logo">
                <span>SNACK IN</span>
            </a>
            <nav class="main-nav">
                <ul>
                    <li><a href="../user_ui.php">Home</a></li>
                    <li><a href="../about.html">About</a></li>
                    <li><a href="../shop.html">Shop</a></li>
                    <li><a href="../contact.html">Contact</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <div class="action-icon">
                    <a href="cart.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="badge cart-count"><?php echo count($cart); ?></span>
                    </a>
                </div>
                <div class="action-icon">
                    <a href="../history.php">
                        <i class="fas fa-history"></i>
                    </a>
                </div>
                <a href="../logout.php" class="action-icon logout-icon">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
                <div class="mobile-menu-toggle" id="mobile-menu-toggle">
                    <i class="fas fa-bars"></i>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobile-menu" style="position: fixed; top: 0; right: -300px; width: 300px; height: 100%; background-color: white; z-index: 2000; padding: 20px; box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1); transition: right 0.3s ease; overflow-y: auto;">
        <div class="mobile-menu-close" id="mobile-menu-close" style="text-align: right; margin-bottom: 20px; font-size: 24px; cursor: pointer;">
            <i class="fas fa-times"></i>
        </div>
        <ul>
            <li><a href="../user_ui.php" style="display: block; padding: 10px 0; border-bottom: 1px solid #eee; color: #333; font-weight: 500;">Home</a></li>
            <li><a href="../about.html" style="display: block; padding: 10px 0; border-bottom: 1px solid #eee; color: #333; font-weight: 500;">About</a></li>
            <li><a href="../shop.html" style="display: block; padding: 10px 0; border-bottom: 1px solid #eee; color: #333; font-weight: 500;">Shop</a></li>
            <li><a href="../contact.html" style="display: block; padding: 10px 0; border-bottom: 1px solid #eee; color: #333; font-weight: 500;">Contact</a></li>
        </ul>
    </div>
    <div class="mobile-menu-overlay" id="mobile-menu-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 1999; display: none;"></div>

    <!-- Cart Section -->
    <section class="cart-section">
        <div class="container">
            <h1 class="cart-title">Keranjang Belanja</h1>
            <p class="cart-subtitle">Review dan sesuaikan pesanan Anda sebelum checkout</p>

            <?php if (empty($cart)) { ?>
                <div class="cart-empty">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Keranjang Anda Kosong</h3>
                    <p>Sepertinya Anda belum menambahkan produk apapun ke keranjang.</p>
                    <a href="../user_ui.php" class="btn btn-orange mt-4">Belanja Sekarang</a>
                </div>
            <?php } else { ?>
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
                                    <?php foreach ($cart as $item) { ?>
                                        <tr>
                                            <td>
                                                <img src="../<?php echo $item['gambar']; ?>" alt="<?php echo $item['nama']; ?>" class="cart-img">
                                            </td>
                                            <td class="cart-product-name"><?php echo $item['nama']; ?></td>
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
                                    <?php } ?>
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
                                <span class="summary-value">Rp  <?php echo number_format($biaya_admin=$totalHarga * 5/100, 0, ',', '.'); ?></span>
                            </div>
                            <hr>
                            <div class="summary-row">
                                <span class="summary-label">Total</span>
                                <span class="summary-total">Rp <?php echo number_format($totalHarga+ $biaya_admin, 0, ',', '.'); ?></span>
                            </div>

                            <button class="btn btn-orange w-100 mt-4" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                                Checkout Sekarang
                            </button>
                        </div>

                        <div class="cart-actions mt-4">
                            <a href="../user_ui.php" class="btn w-100">
                                <i class="fas fa-arrow-left me-2"></i> Lanjutkan Belanja
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </section>

    <!-- Modal Checkout -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkoutModalLabel">Form Checkout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                        <button type="button" class="btn" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-orange">Bayar Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Notification Element -->
    <div id="cart-notification"></div>

    <!-- Script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Remove item from cart
            $(".remove-item").click(function() {
                var id = $(this).data("id");
                $.post("../include/cart_action.php", {
                    action: "remove",
                    id: id
                }, function(response) {
                    try {
                        var result = JSON.parse(response);
                        if(result.success) {
                            location.reload();
                        } else {
                            showNotification("Gagal menghapus item: " + (result.error || "Unknown error"));
                        }
                    } catch(e) {
                        console.error("Error parsing response:", response);
                        location.reload();
                    }
                });
            });

            // Increase item quantity
            $(".increase").click(function() {
                var id = $(this).data("id");
                $.post("../include/cart_action.php", {
                    action: "increase",
                    id: id
                }, function(response) {
                    try {
                        var result = JSON.parse(response);
                        if(result.success) {
                            location.reload();
                        } else {
                            showNotification("Gagal menambah jumlah: " + (result.error || "Unknown error"));
                        }
                    } catch(e) {
                        console.error("Error parsing response:", response);
                        location.reload();
                    }
                });
            });

            // Decrease item quantity
            $(".decrease").click(function() {
                var id = $(this).data("id");
                $.post("../include/cart_action.php", {
                    action: "decrease",
                    id: id
                }, function(response) {
                    try {
                        var result = JSON.parse(response);
                        if(result.success) {
                            location.reload();
                        } else {
                            showNotification("Gagal mengurangi jumlah: " + (result.error || "Unknown error"));
                        }
                    } catch(e) {
                        console.error("Error parsing response:", response);
                        location.reload();
                    }
                });
            });

            // Checkout form submission
            $("#checkout-form").submit(function(event) {
                event.preventDefault();
                
                // Show loading notification
                showNotification("Memproses pesanan Anda...");
                
                $.post("../include/cart_action.php", $(this).serialize() + "&action=checkout", function(response) {
                    try {
                        var result = JSON.parse(response);
                        if(result.success) {
                            showNotification("Pesanan berhasil disimpan! Status: Sedang diproses");
                            setTimeout(function() {
                                window.location.href = "../history.php";
                            }, 3000);
                        } else {
                            showNotification("Terjadi kesalahan: " + (result.error || "Unknown error"));
                        }
                    } catch(e) {
                        console.error("Error parsing response:", response);
                        showNotification("Terjadi kesalahan saat memproses pesanan. Silakan coba lagi.");
                    }
                }).fail(function(xhr, status, error) {
                    console.error("AJAX error:", status, error);
                    showNotification("Terjadi kesalahan koneksi. Silakan periksa koneksi internet Anda.");
                });
            });

            // Payment method change
            $("#metode_pembayaran").on("change", function() {
                let metode = $(this).val();
                let infoContainer = $("#info-pembayaran");

                if (metode === "Qris") {
                    infoContainer.html(`
                        <h4 class="payment-info-title">Pembayaran via QRIS</h4>
                        <img src="../assets/img/qris-code.png" alt="QRIS Code" class="payment-qr">
                        <p class="payment-note">Scan QR code di atas menggunakan aplikasi e-wallet atau mobile banking Anda. Setelah pembayaran berhasil, klik "Bayar Sekarang".</p>
                    `).show();
                } else if (metode === "Transfer Bank") {
                    infoContainer.html(`
                        <h4 class="payment-info-title">Transfer Bank</h4>
                        <div class="payment-account">
                            <p><strong>Bank BCA</strong></p>
                            <p class="payment-account-number">1234567890</p>
                            <p>a.n. SNACK IN</p>
                        </div>
                        <p class="payment-note">Silakan transfer ke rekening di atas. Setelah transfer berhasil, klik "Bayar Sekarang".</p>
                    `).show();
                } else if (metode === "Cash") {
                    infoContainer.html(`
                        <h4 class="payment-info-title">Cash on Delivery (COD)</h4>
                        <p class="payment-note">Anda akan membayar saat pesanan tiba. Pastikan alamat pengiriman sudah benar.</p>
                    `).show();
                } else {
                    infoContainer.hide();
                }
            });
            
            // Show notification
            function showNotification(message) {
                // Create notification element if it doesn't exist
                if (!document.getElementById('cart-notification')) {
                    const notification = document.createElement('div');
                    notification.id = 'cart-notification';
                    document.body.appendChild(notification);
                }

                const notification = document.getElementById('cart-notification');
                notification.textContent = message;
                notification.style.opacity = '1';
                notification.style.transform = 'translateX(-50%) translateY(0)';
                notification.style.zIndex = '9999'; // Ensure it's on top

                // Hide notification after 3 seconds
                setTimeout(() => {
                    notification.style.opacity = '0';
                    notification.style.transform = 'translateX(-50%) translateY(-20px)';
                }, 3000);
            }

            // Mobile menu functionality
            const mobileMenuToggle = document.getElementById("mobile-menu-toggle");
            const mobileMenu = document.getElementById("mobile-menu");
            const mobileMenuClose = document.getElementById("mobile-menu-close");
            const mobileMenuOverlay = document.getElementById("mobile-menu-overlay");

            // Toggle mobile menu
            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener("click", () => {
                    if (mobileMenu) {
                        mobileMenu.style.right = "0";
                        if (mobileMenuOverlay) {
                            mobileMenuOverlay.style.display = "block";
                        }
                        document.body.style.overflow = "hidden"; // Prevent scrolling
                    }
                });
            }

            // Close mobile menu
            function closeMobileMenu() {
                if (mobileMenu) {
                    mobileMenu.style.right = "-300px";
                    if (mobileMenuOverlay) {
                        mobileMenuOverlay.style.display = "none";
                    }
                    document.body.style.overflow = ""; // Enable scrolling
                }
            }

            if (mobileMenuClose) {
                mobileMenuClose.addEventListener("click", closeMobileMenu);
            }
            if (mobileMenuOverlay) {
                mobileMenuOverlay.addEventListener("click", closeMobileMenu);
            }
            
            // Update cart count on page load
            $.post("../include/cart_action.php", {
                action: "count"
            }, function(count) {
                $(".cart-count").text(count);
            });
        });
    </script>
</body>

</html>