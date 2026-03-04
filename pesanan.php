<?php
// Define admin access constant
define('ADMIN_ACCESS', true);

// Cek akses admin
require_once 'config/security.php';
requireAdmin();

require "config/database.php";

// Handle viewing order details
$view_order = null;
$order_items = null;
if (isset($_GET['view']) && $_GET['view'] > 0) {
    $view_id = (int) $_GET['view'];
    $view_query = "SELECT o.* FROM orders o WHERE o.id = ?";
    $stmt = mysqli_prepare($db, $view_query);
    mysqli_stmt_bind_param($stmt, 'i', $view_id);
    mysqli_stmt_execute($stmt);
    $view_result = mysqli_stmt_get_result($stmt);

    if ($view_result && mysqli_num_rows($view_result) > 0) {
        $view_order = mysqli_fetch_assoc($view_result);

        // Get order items
        $items_query = "SELECT * FROM order_items WHERE order_id = ?";
        $items_stmt = mysqli_prepare($db, $items_query);
        mysqli_stmt_bind_param($items_stmt, 'i', $view_id);
        mysqli_stmt_execute($items_stmt);
        $order_items = mysqli_stmt_get_result($items_stmt);
    }
}

// Handle status update
if (isset($_POST['update_status']) && isset($_POST['order_id'])) {
    $order_id = (int) $_POST['order_id'];
    $new_status = $_POST['status'];

    // Validate status
    $allowed_statuses = ['pending', 'processing', 'completed', 'cancelled'];
    if (in_array($new_status, $allowed_statuses)) {
        $update_query = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($db, $update_query);
        mysqli_stmt_bind_param($stmt, 'si', $new_status, $order_id);

        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Status pesanan berhasil diupdate!";

            // Refresh the view_order data if we're viewing this order
            if ($view_order && $view_order['id'] == $order_id) {
                $view_order['status'] = $new_status;
            }
        } else {
            $error_message = "Gagal mengupdate status pesanan!";
        }
    } else {
        $error_message = "Status tidak valid!";
    }
}

// Get all orders with order items (if not viewing specific order)
if (!$view_order) {
    $orders_query = "SELECT o.* FROM orders o ORDER BY o.created_at DESC";
    $orders_result = mysqli_query($db, $orders_query);
}

$page_title = "Manajemen Pesanan";
include 'include/admin_header.php';
?>

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
    }

    .orders-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .stat-item {
        background: var(--white);
        border-radius: var(--radius-lg);
        padding: 25px;
        text-align: center;
        box-shadow: var(--shadow-sm);
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .stat-item::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: var(--primary-color);
        opacity: 0.05;
        border-radius: 50%;
        transform: translate(30px, -30px);
        transition: var(--transition);
    }

    .stat-item:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
    }

    .stat-item:hover::before {
        transform: translate(20px, -20px) scale(1.1);
        opacity: 0.08;
    }

    .stat-number {
        font-family: 'Outfit', sans-serif;
        font-size: 36px;
        font-weight: 800;
        color: var(--text-dark);
        display: block;
        margin-bottom: 5px;
        line-height: 1.1;
        letter-spacing: -1px;
    }

    .stat-label {
        color: var(--text-muted);
        font-size: 14px;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .detail-container {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-bottom: 30px;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .detail-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: var(--white);
        padding: 25px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .detail-body {
        padding: 30px;
    }

    .info-section {
        background: var(--light-bg);
        border-radius: var(--radius-md);
        padding: 25px;
        margin-bottom: 25px;
        border-left: 4px solid var(--primary-color);
    }

    .info-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 18px;
        color: var(--text-dark);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .info-table {
        width: 100%;
        border-collapse: collapse;
    }

    .info-table th {
        text-align: left;
        padding: 10px 0;
        font-weight: 600;
        color: var(--text-muted);
        width: 40%;
    }

    .info-table td {
        padding: 10px 0;
        color: var(--text-dark);
    }

    .status-update-form {
        background: #fffbeb;
        border-radius: var(--radius-md);
        padding: 25px;
        border-left: 4px solid #f59e0b;
        box-shadow: var(--shadow-sm);
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .items-table th {
        background: var(--light-bg);
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: var(--text-muted);
        border-bottom: 2px solid rgba(0, 0, 0, 0.05);
    }

    .items-table td {
        padding: 15px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        vertical-align: middle;
    }

    .total-section {
        background: var(--light-bg);
        padding: 25px;
        border-radius: var(--radius-md);
        margin-top: 30px;
        text-align: right;
    }

    .total-section div {
        margin-bottom: 10px;
        color: var(--text-muted);
    }

    .total-amount {
        font-family: 'Outfit', sans-serif;
        font-size: 28px;
        font-weight: 800;
        color: var(--text-dark);
        margin-top: 15px !important;
        padding-top: 15px;
        border-top: 2px solid rgba(0, 0, 0, 0.05);
    }

    @media (max-width: 768px) {
        .orders-stats {
            grid-template-columns: repeat(2, 1fr);
        }

        .detail-header {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1 class="page-title">
            <i class="fas fa-clipboard-list"></i>
            <?php echo $view_order ? "Detail Pesanan #" . $view_order['id'] : "Manajemen Pesanan"; ?>
        </h1>
        <p class="page-subtitle">
            <?php echo $view_order ? "Detail lengkap dan update status pesanan" : "Kelola dan pantau semua pesanan pelanggan"; ?>
        </p>
    </div>
</div>

<div class="container-fluid">
    <!-- Navigation Buttons -->
    <div class="nav-buttons">
        <a href="index.php" class="nav-btn">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="data_barang.php" class="nav-btn">
            <i class="fas fa-box"></i> Data Barang
        </a>
        <a href="kategori.php" class="nav-btn">
            <i class="fas fa-tags"></i> Kategori
        </a>
        <a href="pesanan.php" class="nav-btn active">
            <i class="fas fa-shopping-cart"></i> Pesanan
        </a>
        <a href="user.php" class="nav-btn">
            <i class="fas fa-users"></i> User
        </a>
        <a href="history_admin.php" class="nav-btn">
            <i class="fas fa-history"></i> History
        </a>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <?php if ($view_order): ?>
        <!-- Detail View -->
        <div class="detail-container">
            <div class="detail-header">
                <h3><i class="fas fa-info-circle"></i> Detail Pesanan #<?php echo $view_order['id']; ?></h3>
                <a href="pesanan.php" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="detail-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-section">
                            <div class="info-title">
                                <i class="fas fa-shopping-cart"></i> Informasi Pesanan
                            </div>
                            <table class="info-table">
                                <tr>
                                    <th>ID Pesanan</th>
                                    <td><strong>#<?php echo $view_order['id']; ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Tanggal</th>
                                    <td><?php echo date('d M Y, H:i', strtotime($view_order['created_at'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Metode Pembayaran</th>
                                    <td>
                                        <?php
                                        $method = $view_order['metode_pembayaran'];
                                        $badge_class = 'bg-primary';
                                        if (strpos(strtolower($method), 'cod') !== false || strpos(strtolower($method), 'cash') !== false)
                                            $badge_class = 'bg-warning';
                                        if (strpos(strtolower($method), 'transfer') !== false)
                                            $badge_class = 'bg-info';
                                        if (strpos(strtolower($method), 'qris') !== false)
                                            $badge_class = 'bg-success';
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo htmlspecialchars($method); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <?php
                                        $status = $view_order['status'] ?? 'pending';
                                        $status_class = 'bg-info';
                                        if ($status == 'completed')
                                            $status_class = 'bg-success';
                                        if ($status == 'cancelled')
                                            $status_class = 'bg-danger';
                                        if ($status == 'processing')
                                            $status_class = 'bg-warning';
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Total</th>
                                    <td><strong style="color: #00227c;">Rp
                                            <?php echo number_format($view_order['total_harga'] + ($view_order['biaya_admin'] ?? 0), 0, ',', '.'); ?></strong>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-section">
                            <div class="info-title">
                                <i class="fas fa-user"></i> Informasi Pelanggan
                            </div>
                            <table class="info-table">
                                <tr>
                                    <th>Nama</th>
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
                            </table>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="status-update-form">
                            <div class="info-title">
                                <i class="fas fa-cog"></i> Update Status
                            </div>
                            <form method="POST">
                                <input type="hidden" name="order_id" value="<?php echo $view_order['id']; ?>">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status Pesanan</label>
                                    <select name="status" id="status" class="form-select">
                                        <option value="pending" <?php echo ($view_order['status'] ?? 'pending') == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processing" <?php echo ($view_order['status'] ?? '') == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="completed" <?php echo ($view_order['status'] ?? '') == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo ($view_order['status'] ?? '') == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                                <button type="submit" name="update_status" class="btn btn-primary w-100">
                                    <i class="fas fa-save"></i> Update Status
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="info-section">
                    <div class="info-title">
                        <i class="fas fa-box"></i> Item Pesanan
                    </div>
                    <table class="items-table">
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

                <div class="total-section">
                    <div>Subtotal: Rp <?php echo number_format($view_order['total_harga'], 0, ',', '.'); ?></div>
                    <div>Biaya Admin: Rp <?php echo number_format($view_order['biaya_admin'] ?? 0, 0, ',', '.'); ?></div>
                    <div class="total-amount">
                        Total Pesanan: Rp
                        <?php echo number_format($view_order['total_harga'] + ($view_order['biaya_admin'] ?? 0), 0, ',', '.'); ?>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="pesanan.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="button" class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print"></i> Cetak
                    </button>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- List View -->
        <div class="content-container">
            <div class="orders-stats">
                <?php
                $total_orders = mysqli_num_rows($orders_result);
                $pending_orders = mysqli_num_rows(mysqli_query($db, "SELECT * FROM orders WHERE status = 'pending' OR status IS NULL"));
                $processing_orders = mysqli_num_rows(mysqli_query($db, "SELECT * FROM orders WHERE status = 'processing'"));
                $completed_orders = mysqli_num_rows(mysqli_query($db, "SELECT * FROM orders WHERE status = 'completed'"));
                $cancelled_orders = mysqli_num_rows(mysqli_query($db, "SELECT * FROM orders WHERE status = 'cancelled'"));
                ?>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $total_orders; ?></span>
                    <div class="stat-label">Total</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $pending_orders; ?></span>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $processing_orders; ?></span>
                    <div class="stat-label">Processing</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $completed_orders; ?></span>
                    <div class="stat-label">Completed</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $cancelled_orders; ?></span>
                    <div class="stat-label">Cancelled</div>
                </div>
            </div>

            <div class="table-container"
                style="background: var(--white); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); padding: 30px; border: 1px solid rgba(0,0,0,0.05);">
                <table class="enhanced-table table table-hover"
                    style="width: 100%; border-collapse: collapse; margin-bottom: 1rem; color: var(--text-dark);">
                    <thead
                        style="background: var(--light-bg); text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px;">
                        <tr>
                            <th
                                style="padding: 1rem; border-bottom: 2px solid rgba(0,0,0,0.05); text-align: left; font-weight: 700; color: var(--text-muted);">
                                ID</th>
                            <th
                                style="padding: 1rem; border-bottom: 2px solid rgba(0,0,0,0.05); text-align: left; font-weight: 700; color: var(--text-muted);">
                                Pelanggan</th>
                            <th
                                style="padding: 1rem; border-bottom: 2px solid rgba(0,0,0,0.05); text-align: left; font-weight: 700; color: var(--text-muted);">
                                Alamat</th>
                            <th
                                style="padding: 1rem; border-bottom: 2px solid rgba(0,0,0,0.05); text-align: left; font-weight: 700; color: var(--text-muted);">
                                Pembayaran</th>
                            <th
                                style="padding: 1rem; border-bottom: 2px solid rgba(0,0,0,0.05); text-align: left; font-weight: 700; color: var(--text-muted);">
                                Status</th>
                            <th
                                style="padding: 1rem; border-bottom: 2px solid rgba(0,0,0,0.05); text-align: left; font-weight: 700; color: var(--text-muted);">
                                Total</th>
                            <th
                                style="padding: 1rem; border-bottom: 2px solid rgba(0,0,0,0.05); text-align: left; font-weight: 700; color: var(--text-muted);">
                                Tanggal</th>
                            <th
                                style="padding: 1rem; border-bottom: 2px solid rgba(0,0,0,0.05); text-align: center; font-weight: 700; color: var(--text-muted);">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($orders_result) > 0):
                            ?>
                            <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                                <tr style="border-bottom: 1px solid rgba(0,0,0,0.05); transition: var(--transition-fast);">
                                    <td style="padding: 1rem;"><strong
                                            style="color: var(--primary-color);">#<?php echo $order['id']; ?></strong></td>
                                    <td style="padding: 1rem; font-weight: 500;">
                                        <?php echo htmlspecialchars($order['nama']); ?><br>
                                        <small
                                            style="color: var(--text-muted); font-size: 0.85rem;"><?php echo htmlspecialchars($order['username'] ?? 'N/A'); ?></small>
                                    </td>
                                    <td style="padding: 1rem; font-size: 0.9rem; color: var(--text-muted);">
                                        <?php echo htmlspecialchars(substr($order['alamat'], 0, 30)) . (strlen($order['alamat']) > 30 ? '...' : ''); ?>
                                    </td>
                                    <td style="padding: 1rem;">
                                        <?php
                                        $method = $order['metode_pembayaran'];

                                        $bg_pay = '#e0e7ff';
                                        $col_pay = '#4f46e5';
                                        if (strpos(strtolower($method), 'cod') !== false || strpos(strtolower($method), 'cash') !== false) {
                                            $bg_pay = '#fef3c7';
                                            $col_pay = '#d97706';
                                        }
                                        if (strpos(strtolower($method), 'transfer') !== false) {
                                            $bg_pay = '#e0f2fe';
                                            $col_pay = '#0ea5e9';
                                        }
                                        if (strpos(strtolower($method), 'qris') !== false) {
                                            $bg_pay = '#d1fae5';
                                            $col_pay = '#059669';
                                        }
                                        ?>
                                        <span
                                            style="background: <?php echo $bg_pay; ?>; color: <?php echo $col_pay; ?>; padding: 6px 12px; border-radius: 50px; font-size: 0.8rem; font-weight: 600; display: inline-block;">
                                            <?php echo htmlspecialchars($method); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 1rem;">
                                        <?php
                                        $status = $order['status'] ?? 'pending';
                                        $bg_stat = '#e0f2fe';
                                        $col_stat = '#0ea5e9'; // info
                                        if ($status == 'completed') {
                                            $bg_stat = '#d1fae5';
                                            $col_stat = '#059669';
                                        } // success
                                        if ($status == 'cancelled') {
                                            $bg_stat = '#fee2e2';
                                            $col_stat = '#dc2626';
                                        } // danger
                                        if ($status == 'processing') {
                                            $bg_stat = '#fef3c7';
                                            $col_stat = '#d97706';
                                        } // warning
                                        ?>
                                        <span
                                            style="background: <?php echo $bg_stat; ?>; color: <?php echo $col_stat; ?>; padding: 6px 12px; border-radius: 50px; font-size: 0.8rem; font-weight: 600; display: inline-block;">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 1rem;"><strong style="font-family: 'Outfit', sans-serif;">Rp
                                            <?php echo number_format($order['total_harga'] + ($order['biaya_admin'] ?? 0), 0, ',', '.'); ?></strong>
                                    </td>
                                    <td style="padding: 1rem; color: var(--text-muted); font-size: 0.9rem;">
                                        <?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?>
                                    </td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <a href="pesanan.php?view=<?php echo $order['id']; ?>" class="btn btn-sm"
                                            style="display: inline-block; padding: 8px; background: var(--primary-color); color: white; border-radius: 8px; transition: var(--transition);">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center" style="padding: 60px 40px; text-align: center;">
                                    <div
                                        style="width: 80px; height: 80px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                                        <i class="fas fa-inbox" style="font-size: 32px; color: #94a3b8;"></i>
                                    </div>
                                    <h4
                                        style="color: var(--text-dark); font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 700; margin-bottom: 8px;">
                                        Belum ada pesanan</h4>
                                    <p style="color: var(--text-muted); margin: 0;">Pesanan akan muncul di sini setelah
                                        pelanggan melakukan pembelian.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'include/admin_footer.php'; ?>