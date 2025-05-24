<?php
// Start session and include database connection
session_start();
require "config/database.php";

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get total orders count
$count_query = "SELECT COUNT(*) as total FROM orders";
$count_result = mysqli_query($db, $count_query);
$total_orders = $count_result ? mysqli_fetch_assoc($count_result)['total'] : 0;
$total_pages = ceil($total_orders / $per_page);

// Get orders with pagination
$query = "SELECT o.* 
          FROM orders o 
          ORDER BY o.created_at DESC 
          LIMIT $offset, $per_page";
$result = mysqli_query($db, $query);

// Handle viewing order details
$view_order = null;
$order_items = null;
if (isset($_GET['view']) && $_GET['view'] > 0) {
    $view_id = (int)$_GET['view'];
    $view_query = "SELECT o.* 
              FROM orders o 
              WHERE o.id = $view_id";
    $view_result = mysqli_query($db, $view_query);

    if ($view_result && mysqli_num_rows($view_result) > 0) {
        $view_order = mysqli_fetch_assoc($view_result);

        // Get order items
        $items_query = "SELECT * FROM order_items WHERE order_id = $view_id";
        $order_items = mysqli_query($db, $items_query);
    }
}

// Function to get payment method badge
function getPaymentBadge($method) {
    switch ($method) {
        case 'transfer_bank':
        case 'Transfer Bank':
            return '<span class="badge bg-blue">Transfer Bank</span>';
        case 'cod':
        case 'Cash':
            return '<span class="badge bg-amber">COD</span>';
        case 'e-wallet':
        case 'Qris':
            return '<span class="badge bg-teal">E-Wallet/QRIS</span>';
        default:
            return '<span class="badge bg-gray">' . htmlspecialchars($method) . '</span>';
    }
}

// Function to get status badge
function getStatusBadge($status) {
    switch ($status) {
        case 'processing':
            return '<span class="badge bg-info">Sedang Diproses</span>';
        case 'delivered':
            return '<span class="badge bg-success">Selesai</span>';
        default:
            return '<span class="badge bg-info">Sedang Diproses</span>';
    }
}

// Function to format date
function formatDate($date) {
    return date('d M Y, H:i', strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $view_order ? "Detail Pesanan #" . $view_order['id'] : "Riwayat Pesanan (Admin)"; ?> - Snack In</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* Base Styles */
        :root {
            --primary: #00227c;
            --primary-light: #eef2ff;
            --secondary: #3f3d56;
            --success: #10b981;
            --success-light: #ecfdf5;
            --warning: #f59e0b;
            --warning-light: #fffbeb;
            --danger: #ef4444;
            --danger-light: #fef2f2;
            --info: #06b6d4;
            --info-light: #ecfeff;
            --gray: #6b7280;
            --gray-light: #f9fafb;
            --border: #e5e7eb;
            --text-dark: #1f2937;
            --text-muted: #6b7280;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            background-color: #f8fafc;
        }

        /* Card Styles */
        .card-modern {
            background: white;
            border-radius: 12px;
            border: none;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .card-modern:hover {
            box-shadow: var(--shadow-md);
        }

        .card-header-modern {
            background: var(--primary);
            color: white;
            padding: 16px 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: none;
        }

        .card-header-modern i {
            margin-right: 10px;
        }

        .card-body-modern {
            padding: 24px;
        }

        /* Info Box Styles */
        .info-box {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
        }

        .info-box-primary {
            border-left: 4px solid var(--primary);
        }

        .info-box-success {
            border-left: 4px solid var(--success);
        }

        .info-box-warning {
            border-left: 4px solid var(--warning);
        }

        /* Section Title */
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 16px;
            color: var(--secondary);
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 8px;
            color: var(--primary);
        }

        /* Table Styles */
        .table-modern {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-modern th {
            background-color: var(--gray-light);
            font-weight: 600;
            color: var(--secondary);
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .table-modern td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .table-modern tbody tr:hover {
            background-color: var(--primary-light);
        }

        .table-modern tbody tr:last-child td {
            border-bottom: none;
        }

        /* Badge Styles */
        .badge {
            padding: 6px 10px;
            font-weight: 500;
            border-radius: 6px;
            font-size: 0.75rem;
        }

        .badge.bg-blue {
            background-color: var(--primary);
            color: white;
        }

        .badge.bg-amber {
            background-color: var(--warning);
            color: #7c2d12;
        }

        .badge.bg-teal {
            background-color: var(--info);
            color: white;
        }

        .badge.bg-gray {
            background-color: var(--gray);
            color: white;
        }

        .badge.bg-warning {
            background-color: var(--warning);
            color: #7c2d12;
        }

        .badge.bg-info {
            background-color: var(--info);
            color: white;
        }

        .badge.bg-primary {
            background-color: var(--primary);
            color: white;
        }

        .badge.bg-success {
            background-color: var(--success);
            color: white;
        }

        .badge.bg-danger {
            background-color: var(--danger);
            color: white;
        }

        .badge.bg-secondary {
            background-color: var(--gray);
            color: white;
        }

        /* Button Styles */
        .btn-modern {
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-modern-primary {
            background-color: var(--primary);
            color: white;
            border: none;
        }

        .btn-modern-primary:hover {
            background-color: #3b4fd8;
            color: white;
        }

        .btn-modern-secondary {
            background-color: white;
            color: var(--secondary);
            border: 1px solid var(--border);
        }

        .btn-modern-secondary:hover {
            background-color: var(--gray-light);
            color: var(--secondary);
        }

        .btn-modern-success {
            background-color: var(--success);
            color: white;
            border: none;
        }

        .btn-modern-success:hover {
            background-color: #0d9488;
            color: white;
        }

        .btn-modern-warning {
            background-color: var(--warning);
            color: white;
            border: none;
        }

        .btn-modern-warning:hover {
            background-color: #d97706;
            color: white;
        }

        .btn-modern-danger {
            background-color: var(--danger);
            color: white;
            border: none;
        }

        .btn-modern-danger:hover {
            background-color: #dc2626;
            color: white;
        }

        .btn-modern-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        /* Summary Box */
        .summary-box {
            background-color: var(--gray-light);
            border-radius: 8px;
            padding: 16px;
            margin-top: 24px;
        }

        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .summary-table th {
            padding: 10px;
            text-align: left;
            font-weight: 600;
            color: var(--secondary);
        }

        .summary-table td {
            padding: 10px;
            text-align: right;
        }

        .summary-table .total-row {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--primary);
        }

        /* Pagination */
        .pagination-modern {
            display: flex;
            justify-content: center;
            margin-top: 24px;
            gap: 8px;
        }

        .pagination-modern .page-item {
            list-style: none;
        }

        .pagination-modern .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background-color: white;
            color: var(--secondary);
            border: 1px solid var(--border);
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .pagination-modern .page-link:hover {
            background-color: var(--primary-light);
            color: var(--primary);
        }

        .pagination-modern .page-item.active .page-link {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination-modern .page-item.disabled .page-link {
            color: var(--gray);
            pointer-events: none;
            background-color: var(--gray-light);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--gray);
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state h4 {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--secondary);
        }

        .empty-state p {
            color: var(--text-muted);
            max-width: 400px;
            margin: 0 auto;
        }

        /* Status Update Form */
        .status-update-form {
            margin-top: 20px;
            padding: 16px;
            background-color: var(--gray-light);
            border-radius: 8px;
        }

        .status-update-form select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid var(--border);
            background-color: white;
            margin-right: 10px;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .card-body-modern {
                padding: 16px;
            }

            .table-modern th,
            .table-modern td {
                padding: 10px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 fw-bold"><?php echo $view_order ? "Detail Pesanan #" . $view_order['id'] : "Riwayat Pesanan (Admin)"; ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active"><?php echo $view_order ? "Detail Pesanan" : "Riwayat Pesanan"; ?></li>
                </ol>
            </nav>
        </div>

        <?php if ($view_order): ?>
            <!-- Detail Pesanan View -->
            <div class="card-modern">
                <div class="card-header-modern">
                    <div>
                        <i class="fas fa-info-circle"></i>
                        Detail Pesanan #<?php echo $view_order['id']; ?>
                    </div>
                    <a href="history_admin.php" class="btn-modern btn-modern-secondary btn-modern-sm">
                        <i class="fas fa-arrow-left"></i>
                        Kembali
                    </a>
                </div>
                <div class="card-body-modern">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-box info-box-primary">
                                <h5 class="section-title">
                                    <i class="fas fa-shopping-cart"></i>
                                    Informasi Pesanan
                                </h5>
                                <table class="table-modern">
                                    <tbody>
                                        <tr>
                                            <th width="40%">ID Pesanan</th>
                                            <td><strong>#<?php echo $view_order['id']; ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal Pesanan</th>
                                            <td><?php echo formatDate($view_order['created_at']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Metode Pembayaran</th>
                                            <td><?php echo getPaymentBadge($view_order['metode_pembayaran']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td><?php echo getStatusBadge($view_order['status'] ?? 'pending'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Total</th>
                                            <td><span style="color: var(--primary); font-weight: 700;">Rp <?php echo number_format($view_order['total_harga'], 0, ',', '.'); ?></span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box info-box-success">
                                <h5 class="section-title">
                                    <i class="fas fa-user"></i>
                                    Informasi Pelanggan
                                </h5>
                                <table class="table-modern">
                                    <tbody>
                                        <tr>
                                            <th width="40%">Nama</th>
                                            <td><strong><?php echo htmlspecialchars($view_order['nama']); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>Username</th>
                                            <td><?php echo htmlspecialchars($view_order['username'] ?? 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Alamat</th>
                                            <td><?php echo nl2br(htmlspecialchars($view_order['alamat'])); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box info-box-warning">
                                <h5 class="section-title">
                                    <i class="fas fa-cog"></i>
                                    Update Status
                                </h5>
                                <form action="update_order_status.php" method="POST" class="status-update-form">
                                    <input type="hidden" name="order_id" value="<?php echo $view_order['id']; ?>">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status Pesanan</label>
                                        <select name="status" id="status" class="form-select">
                                            <option value="pending" <?php echo ($view_order['status'] ?? 'pending') == 'pending' ? 'selected' : ''; ?>>Menunggu Konfirmasi</option>
                                            <option value="processing" <?php echo ($view_order['status'] ?? '') == 'processing' ? 'selected' : ''; ?>>Sedang Diproses</option>
                                            <option value="shipped" <?php echo ($view_order['status'] ?? '') == 'shipped' ? 'selected' : ''; ?>>Dalam Pengiriman</option>
                                            <option value="delivered" <?php echo ($view_order['status'] ?? '') == 'delivered' ? 'selected' : ''; ?>>Pesanan Selesai</option>
                                            <option value="cancelled" <?php echo ($view_order['status'] ?? '') == 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn-modern btn-modern-primary w-100">
                                        <i class="fas fa-save"></i> Update Status
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <h5 class="section-title mt-4">
                        <i class="fas fa-box"></i>
                        Item Pesanan
                    </h5>
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>ID Produk</th>
                                    <th>Nama Produk</th>
                                    <th>Harga</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($order_items && mysqli_num_rows($order_items) > 0): ?>
                                    <?php while ($item = mysqli_fetch_assoc($order_items)): ?>
                                        <tr>
                                            <td><?php echo $item['produk_id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($item['nama_produk']); ?></strong></td>
                                            <td>Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                                            <td><?php echo $item['jumlah']; ?></td>
                                            <td><strong>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></strong></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada item</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="summary-box">
                        <div class="row">
                            <div class="col-md-8"></div>
                            <div class="col-md-4">
                                <table class="summary-table">
                                    <tr class="total-row">
                                        <th>Total Pesanan</th>
                                        <td>Rp <?php echo number_format($view_order['total_harga'], 0, ',', '.'); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="history_admin.php" class="btn-modern btn-modern-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Kembali
                        </a>
                        <div>
                            <button type="button" class="btn-modern btn-modern-primary" onclick="window.print()">
                                <i class="fas fa-print"></i>
                                Cetak Pesanan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Daftar Pesanan View -->
            <div class="card-modern">
                <div class="card-header-modern">
                    <div>
                        <i class="fas fa-history"></i>
                        Riwayat Pesanan
                    </div>
                    <div>
                        <span class="badge bg-blue"><?php echo $total_orders; ?> pesanan</span>
                    </div>
                </div>
                <div class="card-body-modern">

                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table-modern">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Pelanggan</th>
                                        <th>Alamat</th>
                                        <th>Metode Pembayaran</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Tanggal</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($order = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><strong>#<?php echo $order['id']; ?></strong></td>
                                            <td><?php echo htmlspecialchars($order['nama']); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($order['username'] ?? 'N/A'); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars(substr($order['alamat'], 0, 30)) . (strlen($order['alamat']) > 30 ? '...' : ''); ?></td>
                                            <td><?php echo getPaymentBadge($order['metode_pembayaran']); ?></td>
                                            <td><?php echo getStatusBadge($order['status'] ?? 'pending'); ?></td>
                                            <td><span style="color: var(--primary); font-weight: 600;">Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></span></td>
                                            <td><?php echo formatDate($order['created_at']); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="history_admin.php?view=<?php echo $order['id']; ?>" class="btn-modern btn-modern-primary btn-modern-sm">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn-modern btn-modern-danger btn-modern-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $order['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>

                                                <!-- Status Update Modal -->
                                                <div class="modal fade" id="statusModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Update Status Pesanan #<?php echo $order['id']; ?></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form action="update_order_status.php" method="POST">
                                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                                    <div class="mb-3">
                                                                        <label for="status<?php echo $order['id']; ?>" class="form-label">Status Pesanan</label>
                                                                        <select name="status" id="status<?php echo $order['id']; ?>" class="form-select">
                                                                            <option value="pending" <?php echo ($order['status'] ?? 'pending') == 'pending' ? 'selected' : ''; ?>>Menunggu Konfirmasi</option>
                                                                            <option value="processing" <?php echo ($order['status'] ?? '') == 'processing' ? 'selected' : ''; ?>>Sedang Diproses</option>
                                                                            <option value="shipped" <?php echo ($order['status'] ?? '') == 'shipped' ? 'selected' : ''; ?>>Dalam Pengiriman</option>
                                                                            <option value="delivered" <?php echo ($order['status'] ?? '') == 'delivered' ? 'selected' : ''; ?>>Pesanan Selesai</option>
                                                                            <option value="cancelled" <?php echo ($order['status'] ?? '') == 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                                                                        </select>
                                                                    </div>
                                                                    <button type="submit" class="btn-modern btn-modern-primary w-100">
                                                                        <i class="fas fa-save"></i> Update Status
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Delete Modal -->
                                                <div class="modal fade" id="deleteModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Hapus Pesanan #<?php echo $order['id']; ?></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Apakah Anda yakin ingin menghapus pesanan ini?</p>
                                                                <p><strong>Perhatian:</strong> Tindakan ini tidak dapat dibatalkan.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn-modern btn-modern-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <a href="delete_order.php?id=<?php echo $order['id']; ?>" class="btn-modern btn-modern-danger">
                                                                    <i class="fas fa-trash"></i> Hapus Pesanan
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-modern">
                                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Empty State -->
                        <div class="empty-state">
                            <i class="fas fa-shopping-cart"></i>
                            <h4>Belum Ada Pesanan</h4>
                            <p>Belum ada pesanan yang tercatat dalam sistem. Pesanan baru akan muncul di sini ketika pelanggan melakukan pembelian.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Print functionality
        function printOrder() {
            window.print();
        }
        
        // Status update confirmation
        document.addEventListener('DOMContentLoaded', function() {
            const statusForms = document.querySelectorAll('.status-update-form');
            statusForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const status = this.querySelector('select[name="status"]').value;
                    const currentStatus = this.querySelector('select[name="status"]').getAttribute('data-current');
                    
                    if (status === 'cancelled' && !confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')) {
                        e.preventDefault();
                        return false;
                    }
                    
                    if (currentStatus !== status) {
                        return confirm('Apakah Anda yakin ingin mengubah status pesanan?');
                    }
                });
            });
        });
    </script>
</body>

</html>
