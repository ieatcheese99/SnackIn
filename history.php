<?php
session_start();
require "config/database.php";

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 5;
$offset = ($page - 1) * $per_page;

// Get total orders count for this user by name
$count_query = "SELECT COUNT(*) as total FROM orders WHERE nama = '$username'";
$count_result = mysqli_query($db, $count_query);
$total_orders = $count_result ? mysqli_fetch_assoc($count_result)['total'] : 0;
$total_pages = ceil($total_orders / $per_page);

// Get user orders with pagination
$orders = [];
$query = "SELECT * FROM orders WHERE nama = '$username' ORDER BY created_at DESC LIMIT $offset, $per_page";
$result = mysqli_query($db, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
}

// Handle viewing order details
$view_order = null;
$order_items = null;
if (isset($_GET['view']) && $_GET['view'] > 0) {
    $view_id = (int)$_GET['view'];
    $view_query = "SELECT * FROM orders WHERE id = $view_id AND nama = '$username'";
    $view_result = mysqli_query($db, $view_query);

    if ($view_result && mysqli_num_rows($view_result) > 0) {
        $view_order = mysqli_fetch_assoc($view_result);

        // Get order items
        $items_query = "SELECT * FROM order_items WHERE order_id = $view_id";
        $order_items = mysqli_query($db, $items_query);
    }
}

// Function to get status badge with icon
function getStatusBadge($status) {
    switch ($status) {
        case 'processing':
            return '<span class="status-badge status-processing"><i class="fas fa-spinner fa-spin"></i> Sedang Diproses</span>';
        case 'delivered':
            return '<span class="status-badge status-delivered"><i class="fas fa-check-circle"></i> Selesai</span>';
        default:
            return '<span class="status-badge status-processing"><i class="fas fa-spinner fa-spin"></i> Sedang Diproses</span>';
    }
}

// Function to format date
function formatDate($date) {
    return date('d M Y, H:i', strtotime($date));
}

// Function to get payment method badge
function getPaymentBadge($method) {
    switch ($method) {
        case 'Transfer Bank':
        case 'transfer_bank':
            return '<span class="payment-badge payment-bank"><i class="fas fa-university"></i> Transfer Bank</span>';
        case 'Qris':
        case 'qris':
        case 'e-wallet':
            return '<span class="payment-badge payment-qris"><i class="fas fa-qrcode"></i> QRIS</span>';
        case 'Cash':
        case 'cash':
        case 'cod':
            return '<span class="payment-badge payment-cash"><i class="fas fa-money-bill-wave"></i> COD</span>';
        default:
            return '<span class="payment-badge payment-other"><i class="fas fa-credit-card"></i> ' . htmlspecialchars($method) . '</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $view_order ? "Detail Pesanan #" . $view_order['id'] : "Riwayat Pesanan"; ?> - Snack In</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

        /* History Page Styles */
        .history-section {
            padding: 40px 0;
            min-height: calc(100vh - 300px);
        }

        .page-title {
            color: #00227c;
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-subtitle {
            color: #666;
            font-size: 16px;
            margin-bottom: 30px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #00227c;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: #f69e22;
        }

        .history-empty {
            text-align: center;
            padding: 40px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .history-empty i {
            font-size: 60px;
            color: #f69e22;
            margin-bottom: 20px;
        }

        .history-empty h3 {
            font-size: 24px;
            margin-bottom: 15px;
            color: #00227c;
        }

        /* Card Styles */
        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }

        .card-header {
            background-color: #00227c;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-weight: 600;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-date {
            font-size: 14px;
            opacity: 0.8;
        }

        .card-body {
            padding: 20px;
        }

        .card-footer {
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Order List Styles */
        .order-list {
            display: grid;
            gap: 20px;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .order-item:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-3px);
        }

        .order-info {
            flex: 1;
        }

        .order-id {
            font-weight: 600;
            color: #00227c;
            margin-bottom: 5px;
            font-size: 16px;
        }

        .order-date {
            color: #666;
            font-size: 14px;
        }

        .order-total {
            font-weight: 700;
            color: #f69e22;
            font-size: 18px;
            margin-left: 20px;
        }

        .order-actions {
            display: flex;
            gap: 10px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 14px;
        }

        /* Status Badge Styles */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .status-pending {
            background-color: #fff7ed;
            color: #c2410c;
        }

        .status-processing {
            background-color: #eff6ff;
            color: #1d4ed8;
        }

        .status-shipped {
            background-color: #f0fdf4;
            color: #15803d;
        }

        .status-delivered {
            background-color: #ecfdf5;
            color: #047857;
        }

        .status-cancelled {
            background-color: #fef2f2;
            color: #b91c1c;
        }

        /* Payment Badge Styles */
        .payment-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .payment-bank {
            background-color: #f0f9ff;
            color: #0369a1;
        }

        .payment-qris {
            background-color: #f5f3ff;
            color: #6d28d9;
        }

        .payment-cash {
            background-color: #f0fdf4;
            color: #15803d;
        }

        .payment-other {
            background-color: #f3f4f6;
            color: #4b5563;
        }

        /* Order Detail Styles */
        .order-detail-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .order-detail-header {
            background-color: #00227c;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-detail-title {
            font-weight: 600;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .order-detail-body {
            padding: 20px;
        }

        .order-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-box {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            border-left: 4px solid #00227c;
        }

        .info-box-title {
            font-weight: 600;
            color: #00227c;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-box-content {
            color: #333;
        }

        .order-items-title {
            font-weight: 600;
            color: #00227c;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .order-items-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 20px;
        }

        .order-items-table th {
            background-color: #f8f9fa;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #00227c;
            border-bottom: 1px solid #eee;
        }

        .order-items-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        .order-items-table tr:last-child td {
            border-bottom: none;
        }

        .product-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .product-image {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
        }

        .product-name {
            font-weight: 500;
            color: #00227c;
        }

        .price-cell {
            color: #f69e22;
            font-weight: 500;
        }

        .quantity-cell {
            text-align: center;
        }

        .subtotal-cell {
            font-weight: 600;
            color: #00227c;
        }

        .order-summary {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .summary-row:last-child {
            margin-bottom: 0;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        .summary-label {
            color: #666;
        }

        .summary-value {
            font-weight: 600;
            color: #00227c;
        }

        .summary-total {
            font-weight: 700;
            color: #f69e22;
            font-size: 18px;
        }

        .order-actions-footer {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        /* Pagination Styles */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 30px;
        }

        .pagination-item {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            border-radius: 8px;
            color: #00227c;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .pagination-item:hover {
            background-color: #f0f4ff;
        }

        .pagination-item.active {
            background-color: #00227c;
            color: white;
        }

        .pagination-item.disabled {
            color: #ccc;
            cursor: not-allowed;
            background-color: #f8f9fa;
            box-shadow: none;
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

            .order-info-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .order-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .order-total {
                margin-left: 0;
            }

            .order-actions {
                width: 100%;
                justify-content: space-between;
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

            .order-items-table {
                display: block;
                overflow-x: auto;
            }
        }

        @media (max-width: 576px) {
            .page-title {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .order-actions-footer {
                flex-direction: column;
                gap: 10px;
            }

            .order-actions-footer .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <!-- Main Header -->
    <header class="main-header">
        <div class="container">
            <a href="user_ui.php" class="logo">
                <img src="assets/img/Logo Bisnis Bengkel Otomotif (3).png" alt="Snack In Logo">
                <span>SNACK IN</span>
            </a>
            <nav class="main-nav">
                <ul>
                    <li><a href="user_ui.php">Home</a></li>
                    <li><a href="about.html">About</a></li>
                    <li><a href="shop.html">Shop</a></li>
                    <li><a href="about.html">Testimoni</a></li>
                    <li><a href="contact.html">Contact</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <div class="action-icon" id="search-toggle">
                    <i class="fas fa-search"></i>
                </div>
                <div class="action-icon">
                    <a href="include/cart.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="badge cart-count">0</span>
                    </a>
                </div>
                <div class="action-icon">
                    <a href="history.php">
                        <i class="fas fa-history"></i>
                    </a>
                </div>
                <a href="logout.php" class="action-icon logout-icon">
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
            <li><a href="user_ui.php" style="display: block; padding: 10px 0; border-bottom: 1px solid #eee; color: #333; font-weight: 500;">Home</a></li>
            <li><a href="about.html" style="display: block; padding: 10px 0; border-bottom: 1px solid #eee; color: #333; font-weight: 500;">About</a></li>
            <li><a href="shop.html" style="display: block; padding: 10px 0; border-bottom: 1px solid #eee; color: #333; font-weight: 500;">Shop</a></li>
            <li><a href="contact.html" style="display: block; padding: 10px 0; border-bottom: 1px solid #eee; color: #333; font-weight: 500;">Contact</a></li>
        </ul>
    </div>
    <div class="mobile-menu-overlay" id="mobile-menu-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 1999; display: none;"></div>

    <!-- History Section -->
    <section class="history-section">
        <div class="container">
            <?php if ($view_order): ?>
                <!-- Order Detail View -->
                <div class="page-title">
                    <h1>Detail Pesanan #<?php echo $view_order['id']; ?></h1>
                    <a href="history.php" class="back-link">
                        <i class="fas fa-arrow-left"></i> Kembali ke Riwayat Pesanan
                    </a>
                </div>

                <div class="order-detail-card">
                    <div class="order-detail-header">
                        <div class="order-detail-title">
                            <i class="fas fa-shopping-bag"></i> Pesanan #<?php echo $view_order['id']; ?>
                        </div>
                        <div><?php echo getStatusBadge($view_order['status'] ?? 'pending'); ?></div>
                    </div>
                    <div class="order-detail-body">
                        <div class="order-info-grid">
                            <div class="info-box">
                                <div class="info-box-title">
                                    <i class="fas fa-info-circle"></i> Status Pesanan
                                </div>
                                <div class="info-box-content">
                                    <?php echo getStatusBadge($view_order['status'] ?? 'pending'); ?>
                                </div>
                            </div>
                            <div class="info-box">
                                <div class="info-box-title">
                                    <i class="fas fa-credit-card"></i> Metode Pembayaran
                                </div>
                                <div class="info-box-content">
                                    <?php echo getPaymentBadge($view_order['metode_pembayaran'] ?? ''); ?>
                                </div>
                            </div>
                            <div class="info-box">
                                <div class="info-box-title">
                                    <i class="fas fa-map-marker-alt"></i> Alamat Pengiriman
                                </div>
                                <div class="info-box-content">
                                    <?php echo nl2br(htmlspecialchars($view_order['alamat'])); ?>
                                </div>
                            </div>
                            <div class="info-box">
                                <div class="info-box-title">
                                    <i class="fas fa-calendar-alt"></i> Tanggal Pesanan
                                </div>
                                <div class="info-box-content">
                                    <?php echo formatDate($view_order['created_at']); ?>
                                </div>
                            </div>
                        </div>

                        <div class="order-items-title">
                            <i class="fas fa-box"></i> Item Pesanan
                        </div>
                        <div class="table-responsive">
                            <table class="order-items-table">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Harga</th>
                                        <th>Jumlah</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($order_items && mysqli_num_rows($order_items) > 0): ?>
                                        <?php while ($item = mysqli_fetch_assoc($order_items)): ?>
                                            <tr>
                                                <td>
                                                    <div class="product-cell">
                                                        <img src="<?php echo $item['gambar_produk']; ?>" alt="<?php echo $item['nama_produk']; ?>" class="product-image">
                                                        <span class="product-name"><?php echo $item['nama_produk']; ?></span>
                                                    </div>
                                                </td>
                                                <td class="price-cell">Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                                                <td class="quantity-cell"><?php echo $item['jumlah']; ?></td>
                                                <td class="subtotal-cell">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" style="text-align: center;">Tidak ada item</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="order-summary">
                            <div class="summary-row">
                                <span class="summary-label">Subtotal</span>
                                <span class="summary-value">Rp <?php echo number_format($view_order['total_harga'], 0, ',', '.'); ?></span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Biaya Pengiriman</span>
                                <span class="summary-value">Gratis</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Total</span>
                                <span class="summary-total">Rp <?php echo number_format($view_order['total_harga'], 0, ',', '.'); ?></span>
                            </div>
                        </div>

                        <div class="order-actions-footer">
                            <a href="history.php" class="btn">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <?php if (($view_order['status'] ?? 'pending') === 'pending'): ?>
                                <button class="btn btn-orange" onclick="cancelOrder(<?php echo $view_order['id']; ?>)">
                                    <i class="fas fa-times"></i> Batalkan Pesanan
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Order List View -->
                <h1 class="page-title">Riwayat Pesanan</h1>
                <p class="page-subtitle">Lihat status dan detail pesanan Anda</p>

                <?php if (empty($orders)): ?>
                    <div class="history-empty">
                        <i class="fas fa-history"></i>
                        <h3>Belum Ada Pesanan</h3>
                        <p>Anda belum memiliki riwayat pesanan. Mulai belanja sekarang!</p>
                        <a href="user_ui.php" class="btn btn-orange mt-4">Belanja Sekarang</a>
                    </div>
                <?php else: ?>
                    <div class="order-list">
                        <?php foreach ($orders as $order): ?>
                            <div class="order-item">
                                <div class="order-info">
                                    <div class="order-id">Pesanan #<?php echo $order['id']; ?></div>
                                    <div class="order-date"><?php echo formatDate($order['created_at']); ?></div>
                                    <div style="margin-top: 10px;">
                                        <?php echo getStatusBadge($order['status'] ?? 'pending'); ?>
                                    </div>
                                </div>
                                <div class="order-total">
                                    Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?>
                                </div>
                                <div class="order-actions">
                                    <a href="history.php?view=<?php echo $order['id']; ?>" class="btn btn-sm">
                                        <i class="fas fa-eye"></i> Lihat Detail
                                    </a>
                                    <?php if (($order['status'] ?? 'pending') === 'pending'): ?>
                                        <button class="btn btn-sm btn-orange" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                            <i class="fas fa-times"></i> Batalkan
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <a href="?page=<?php echo max(1, $page - 1); ?>" class="pagination-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>" class="pagination-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <a href="?page=<?php echo min($total_pages, $page + 1); ?>" class="pagination-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Company Info -->
                <div class="footer-column">
                    <h3>Snack In</h3>
                    <ul class="footer-contact">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>SMK BUDI LUHUR</span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <a href="tel:+6281211734491">0812-1173-4491</a>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:mz.siradj@gmail.com">mz.siradj@gmail.com</a>
                        </li>
                    </ul>
                </div>

                <!-- Products -->
                <div class="footer-column">
                    <h3>Products</h3>
                    <ul>
                        <li><a href="#">Makanan</a></li>
                        <li><a href="#">Minuman</a></li>
                    </ul>
                </div>

                <!-- Further Info -->
                <div class="footer-column">
                    <h3>Further Info</h3>
                    <ul>
                        <li><a href="user_ui.php">Home</a></li>
                        <li><a href="about.html">Testimoni</a></li>
                        <li><a href="shop.html">Shop</a></li>
                        <li><a href="contact.html">Contact</a></li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div class="footer-column footer-newsletter">
                    <h3>Newsletter</h3>
                    <p>Subscribe to our newsletter to get the latest updates and offers.</p>
                    <div class="newsletter-form">
                        <input type="email" placeholder="Your email address">
                        <button type="submit" class="btn">Subscribe</button>
                    </div>
                    <div class="footer-social">
                        <h4>Follow Us</h4>
                        <div class="social-icons">
                            <a href="https://instagram.com" target="_blank"><i class="fab fa-instagram"></i></a>
                            <a href="tel:+6281211734491" target="_blank"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2025 Snack In. All rights reserved. | Designed by <a href="#">Team SnackIn</a></p>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Update cart count
            function updateCartCount() {
                $.ajax({
                    url: "include/cart_action.php",
                    type: "POST",
                    data: {
                        action: "count"
                    },
                    success: function(count) {
                        $(".cart-count").text(count);
                    }
                });
            }

            // Initialize cart count
            updateCartCount();

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
        });

        // Cancel order function
        function cancelOrder(orderId) {
            if (confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')) {
                // Send AJAX request to cancel order
                $.ajax({
                    url: "cancel_order.php",
                    type: "POST",
                    data: {
                        order_id: orderId
                    },
                    success: function(response) {
                        alert('Pesanan berhasil dibatalkan');
                        location.reload();
                    },
                    error: function() {
                        alert('Terjadi kesalahan. Silakan coba lagi.');
                    }
                });
            }
        }
    </script>
</body>

</html>
