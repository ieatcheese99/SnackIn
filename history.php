<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['id']) && !isset($_SESSION['username'])) {
    header("Location: login.php");
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

// Get order history
$query = "SELECT o.*, COUNT(oi.id) as item_count 
          FROM orders o 
          LEFT JOIN order_items oi ON o.id = oi.order_id 
          WHERE o.username = ? 
          GROUP BY o.id 
          ORDER BY o.created_at DESC";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "s", $_SESSION['username']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SnackIn - Order History</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/img/Logo Bisnis Bengkel Otomotif (3).png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        /* Global Variables */
        :root {
            --primary-color: #00227c;
            --secondary-color: #001a5e;
            --accent-color: #f48c06;
            --white: #ffffff;
            --orange: #f69e22;
            --light-bg: #f8fafc;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --radius-md: 12px;
            --radius-lg: 20px;
            --shadow-sm: 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 10px 25px rgba(0, 0, 0, 0.08);
            --transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-bg);
            color: var(--text-dark);
            min-height: 100vh;
        }

        /* Main Header */
        .main-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: var(--white);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow-md);
        }

        .main-header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 800;
            text-decoration: none;
            color: var(--white);
            letter-spacing: -0.5px;
        }

        .logo:hover {
            color: var(--white);
        }

        .logo img {
            height: 40px;
            width: auto;
        }

        .main-nav ul {
            display: flex;
            gap: 30px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .main-nav a {
            font-weight: 500;
            transition: var(--transition);
            position: relative;
            color: var(--white);
            text-decoration: none;
            font-size: 15px;
        }

        .main-nav a:hover {
            color: var(--accent-color);
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
            transition: var(--transition);
            color: var(--white);
            text-decoration: none;
        }

        .action-icon:hover {
            color: var(--accent-color);
            transform: translateY(-2px);
        }

        .badge.cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--accent-color);
            color: var(--white);
            font-size: 10px;
            font-weight: 700;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(244, 140, 6, 0.4);
        }

        /* Modern Order Card */
        .order-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .order-card .card-header {
            background: var(--light-bg);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-id {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 18px;
            color: var(--primary-color);
            margin: 0;
        }

        .order-card .card-body {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .status-badge {
            font-size: 12px;
            padding: 6px 14px;
            font-weight: 600;
            border-radius: 50px;
            min-width: 80px;
            text-align: center;
            display: inline-block;
            letter-spacing: 0.5px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px dashed rgba(0, 0, 0, 0.1);
        }

        .detail-row:last-of-type {
            border-bottom: none;
            margin-bottom: 25px;
            padding-bottom: 0;
        }

        .detail-label {
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 500;
        }

        .detail-value {
            color: var(--text-dark);
            font-weight: 600;
            font-size: 14px;
            text-align: right;
        }

        .total-price {
            font-family: 'Outfit', sans-serif;
            color: var(--primary-color);
            font-size: 20px;
            font-weight: 800;
        }

        .btn-view-details {
            margin-top: auto;
            background: var(--light-bg);
            color: var(--primary-color);
            border: 1px solid rgba(0, 34, 124, 0.1);
            border-radius: var(--radius-md);
            padding: 12px;
            font-weight: 600;
            font-size: 14px;
            transition: var(--transition);
            width: 100%;
        }

        .btn-view-details:hover {
            background: var(--primary-color);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 34, 124, 0.2);
        }

        .modal-body .status-badge,
        #orderStatusDetail .badge {
            font-size: 14px !important;
            padding: 8px 16px !important;
            border-radius: 50px !important;
        }

        .empty-history {
            text-align: center;
            padding: 80px 20px;
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .empty-history i {
            font-size: 64px;
            color: #cbd5e1;
            margin-bottom: 25px;
            display: block;
        }

        .empty-history h4 {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        .empty-history p {
            color: var(--text-muted);
            margin-bottom: 30px;
        }

        .order-items {
            max-height: 250px;
            overflow-y: auto;
            border-radius: var(--radius-md);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .order-items table {
            margin-bottom: 0;
        }

        .order-items th {
            font-weight: 600;
            color: var(--text-muted);
            padding: 12px 15px;
            font-size: 13px;
            text-transform: uppercase;
        }

        .order-items td {
            padding: 15px;
            vertical-align: middle;
            font-size: 14px;
            color: var(--text-dark);
        }

        /* Modal Enhancements */
        .modal-content {
            border: none;
            border-radius: var(--radius-lg);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%) !important;
            padding: 20px 25px;
            border-bottom: none;
        }

        .modal-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            color: var(--white);
        }

        .modal-body {
            padding: 30px;
        }

        .modal-detail-section h6 {
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-detail-section p {
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }

        .modal-detail-section p strong {
            color: var(--text-muted);
            font-weight: 500;
        }

        .modal-detail-section p span {
            font-weight: 600;
            color: var(--text-dark);
            text-align: right;
        }

        .modal-footer {
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            padding: 20px 30px;
        }

        @media (max-width: 768px) {
            .main-nav {
                display: none;
            }

            .col-md-6 {
                margin-bottom: 25px;
            }
        }
    </style>
</head>

<body>
    <?php
    // Set cart count for the header
    $cartCount = 0;
    if (isset($_SESSION['cart'])) {
        $cartCount = count($_SESSION['cart']);
    }
    // FIXED: Include from include directory since user_header.php is there
    include_once 'include/user_header.php';
    ?>

    <!-- Main Content -->
    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4" style="color: #00227c; font-weight: 700;">
                    <i class="fas fa-history me-2"></i>Riwayat Pesanan
                </h2>
            </div>
        </div>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php while ($order = mysqli_fetch_assoc($result)): ?>
                    <div class="col">
                        <div class="order-card">
                            <div class="card-header">
                                <h3 class="order-id">Order #<?php echo $order['id']; ?></h3>
                                <?php
                                $status_class = '';
                                $status = $order['status'] ?? 'pending';

                                $bg_stat = '#e0f2fe';
                                $col_stat = '#0ea5e9'; // info (processing)
                                if ($status == 'completed') {
                                    $bg_stat = '#d1fae5';
                                    $col_stat = '#059669';
                                } // success
                                if ($status == 'cancelled') {
                                    $bg_stat = '#fee2e2';
                                    $col_stat = '#dc2626';
                                } // danger
                                if ($status == 'pending') {
                                    $bg_stat = '#fef3c7';
                                    $col_stat = '#d97706';
                                } // warning
                                ?>
                                <span class="status-badge"
                                    style="background: <?php echo $bg_stat; ?>; color: <?php echo $col_stat; ?>;">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="detail-row">
                                    <span class="detail-label"><i class="far fa-calendar-alt me-2"></i>Tanggal</span>
                                    <span
                                        class="detail-value"><?php echo date('d M Y', strtotime($order['created_at'])); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label"><i class="fas fa-box-open me-2"></i>Jumlah</span>
                                    <span class="detail-value"><?php echo $order['item_count']; ?> item</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label"><i class="far fa-credit-card me-2"></i>Pembayaran</span>
                                    <span
                                        class="detail-value"><?php echo htmlspecialchars($order['metode_pembayaran']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label"><i class="fas fa-wallet me-2"></i>Total</span>
                                    <span class="detail-value total-price">Rp
                                        <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></span>
                                </div>

                                <button class="btn btn-view-details" data-id="<?php echo $order['id']; ?>"
                                    data-bs-toggle="modal" data-bs-target="#orderDetailsModal">
                                    <i class="fas fa-receipt me-2"></i>Lihat Detail Pesanan
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-history">
                <i class="fas fa-history"></i>
                <h4>Belum ada riwayat pesanan</h4>
                <p>Anda belum pernah melakukan pemesanan.</p>
                <a href="user_ui.php" class="btn btn-primary mt-3"
                    style="background: linear-gradient(135deg, var(--accent-color), var(--orange)); border: none; padding: 12px 30px; border-radius: var(--radius-md); font-weight: 600; font-family: 'Outfit', sans-serif;">
                    <i class="fas fa-shopping-bag me-2"></i>Mulai Belanja
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: #00227c; color: white;">
                    <h5 class="modal-title" id="orderDetailsModalLabel">Detail Pesanan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center" id="orderDetailsLoading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Memuat detail pesanan...</p>
                    </div>
                    <div id="orderDetailsContent" style="display: none;">
                        <div class="row mb-4">
                            <div class="col-md-6 modal-detail-section">
                                <h6><i class="fas fa-shopping-bag"></i> Informasi Pesanan</h6>
                                <p><strong>Order ID</strong> <span id="orderIdDetail"></span></p>
                                <p><strong>Tanggal</strong> <span id="orderDateDetail"></span></p>
                                <p><strong>Status</strong> <span id="orderStatusDetail"></span></p>
                                <p><strong>Payment</strong> <span id="orderPaymentDetail"></span></p>
                            </div>
                            <div class="col-md-6 modal-detail-section">
                                <h6><i class="fas fa-shipping-fast"></i> Pengiriman</h6>
                                <p><strong>Nama</strong> <span id="orderNameDetail"></span></p>
                                <p><strong>Alamat</strong> <span id="orderAddressDetail"></span></p>
                                <p><strong>Total</strong> <span id="orderTotalDetail"
                                        style="color: var(--primary-color); font-size: 18px; font-weight: 800; font-family: 'Outfit', sans-serif;"></span>
                                </p>
                            </div>
                        </div>
                        <div class="modal-detail-section">
                            <h6><i class="fas fa-box-open"></i> Item Pesanan</h6>
                            <div class="order-items">
                                <table class="table table-striped">
                                    <thead style="background: #f8f9fa;">
                                        <tr>
                                            <th>Produk</th>
                                            <th>Harga</th>
                                            <th>Jumlah</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="orderItemsDetail">
                                        <!-- Order items will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bootstrap JS Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <script>
            $(document).ready(function () {
                // Update cart count
                $.ajax({
                    url: "include/cart_action.php",
                    type: "POST",
                    data: {
                        action: "count"
                    },
                    success: function (count) {
                        $(".cart-count").text(count);
                    }
                });

                // View order details
                $('.btn-view-details').click(function () {
                    const orderId = $(this).data('id');

                    // Reset modal content
                    $('#orderDetailsContent').hide();
                    $('#orderDetailsLoading').show();

                    // Load order details via AJAX
                    $.ajax({
                        url: 'config/get_order_details.php',
                        type: 'GET',
                        data: {
                            order_id: orderId
                        },
                        dataType: 'json',
                        success: function (response) {
                            $('#orderDetailsLoading').hide();

                            if (response.status === 'success') {
                                // Fill order details
                                $('#orderIdDetail').text('#' + response.order.id);
                                $('#orderDateDetail').text(response.order.order_date);
                                $('#orderNameDetail').text(response.order.nama);
                                $('#orderAddressDetail').text(response.order.alamat);
                                $('#orderPaymentDetail').text(response.order.metode_pembayaran);

                                // Set status with badge
                                let status = response.order.status || 'pending';
                                let bgClass = '#e0f2fe'; let textClass = '#0ea5e9'; // info
                                if (status === 'completed') { bgClass = '#d1fae5'; textClass = '#059669'; }
                                if (status === 'cancelled') { bgClass = '#fee2e2'; textClass = '#dc2626'; }
                                if (status === 'pending') { bgClass = '#fef3c7'; textClass = '#d97706'; }

                                $('#orderStatusDetail').html(`<span class="status-badge" style="background: ${bgClass}; color: ${textClass};">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`);
                                $('#orderTotalDetail').text('Rp ' + parseFloat(response.order.total_harga).toLocaleString('id-ID'));

                                // Fill order items
                                let itemsHtml = '';

                                if (response.items.length > 0) {
                                    response.items.forEach(function (item) {
                                        const subtotal = item.harga * item.jumlah;

                                        itemsHtml += `
                                        <tr>
                                            <td>${item.nama_produk}</td>
                                            <td>Rp ${parseFloat(item.harga).toLocaleString('id-ID')}</td>
                                            <td>${item.jumlah}</td>
                                            <td>Rp ${subtotal.toLocaleString('id-ID')}</td>
                                        </tr>
                                    `;
                                    });
                                } else {
                                    itemsHtml = `
                                    <tr>
                                        <td colspan="4" class="text-center">Tidak ada item ditemukan</td>
                                    </tr>
                                `;
                                }

                                $('#orderItemsDetail').html(itemsHtml);
                                $('#orderDetailsContent').show();
                            } else {
                                alert(response.message);
                            }
                        },
                        error: function (xhr, status, error) {
                            $('#orderDetailsLoading').hide();
                            alert('Terjadi kesalahan: ' + error);
                        }
                    });
                });
            });
        </script>
</body>

</html>