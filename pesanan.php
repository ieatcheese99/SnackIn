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
    $view_id = (int)$_GET['view'];
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
    $order_id = (int)$_POST['order_id'];
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
    .orders-stats {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .stat-item {
        text-align: center;
        padding: 15px 20px;
        background: #f8f9fa;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        border-left: 4px solid #00227c;
    }

    .stat-number {
        font-size: 24px;
        font-weight: 800;
        color: #00227c;
        display: block;
    }

    .stat-label {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .detail-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .detail-header {
        background: #00227c;
        color: white;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .detail-body {
        padding: 30px;
    }

    .info-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        border-left: 4px solid #00227c;
    }

    .info-title {
        font-weight: 600;
        color: #333;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .info-table {
        width: 100%;
        border-collapse: collapse;
    }

    .info-table th {
        text-align: left;
        padding: 8px 0;
        font-weight: 600;
        color: #555;
        width: 40%;
    }

    .info-table td {
        padding: 8px 0;
        color: #333;
    }

    .status-update-form {
        background: #fff3cd;
        border-radius: 8px;
        padding: 20px;
        border-left: 4px solid #f59e0b;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .items-table th {
        background: #f8f9fa;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }

    .items-table td {
        padding: 12px;
        border-bottom: 1px solid #dee2e6;
    }

    .total-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
        text-align: right;
    }

    .total-amount {
        font-size: 24px;
        font-weight: 700;
        color: #00227c;
    }

    .badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .bg-primary { background-color: #007bff; color: white; }
    .bg-success { background-color: #28a745; color: white; }
    .bg-warning { background-color: #ffc107; color: black; }
    .bg-danger { background-color: #dc3545; color: white; }
    .bg-info { background-color: #17a2b8; color: white; }

    @media (max-width: 768px) {
        .orders-stats {
            flex-direction: column;
            gap: 15px;
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
                                        if (strpos(strtolower($method), 'cod') !== false || strpos(strtolower($method), 'cash') !== false) $badge_class = 'bg-warning';
                                        if (strpos(strtolower($method), 'transfer') !== false) $badge_class = 'bg-info';
                                        if (strpos(strtolower($method), 'qris') !== false) $badge_class = 'bg-success';
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
                                        if ($status == 'completed') $status_class = 'bg-success';
                                        if ($status == 'cancelled') $status_class = 'bg-danger';
                                        if ($status == 'processing') $status_class = 'bg-warning';
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Total</th>
                                    <td><strong style="color: #00227c;">Rp <?php echo number_format($view_order['total_harga'] + ($view_order['biaya_admin'] ?? 0), 0, ',', '.'); ?></strong></td>
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
                        Total Pesanan: Rp <?php echo number_format($view_order['total_harga'] + ($view_order['biaya_admin'] ?? 0), 0, ',', '.'); ?>
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

            <div class="table-container">
                <table class="enhanced-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pelanggan</th>
                            <th>Alamat</th>
                            <th>Metode Pembayaran</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (mysqli_num_rows($orders_result) > 0): 
                        ?>
                            <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                            <tr>
                                <td><strong>#<?php echo $order['id']; ?></strong></td>
                                <td>
                                    <?php echo htmlspecialchars($order['nama']); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($order['username'] ?? 'N/A'); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars(substr($order['alamat'], 0, 30)) . (strlen($order['alamat']) > 30 ? '...' : ''); ?></td>
                                <td>
                                    <?php
                                    $method = $order['metode_pembayaran'];
                                    $badge_class = 'bg-primary';
                                    if (strpos(strtolower($method), 'cod') !== false || strpos(strtolower($method), 'cash') !== false) $badge_class = 'bg-warning';
                                    if (strpos(strtolower($method), 'transfer') !== false) $badge_class = 'bg-info';
                                    if (strpos(strtolower($method), 'qris') !== false) $badge_class = 'bg-success';
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo htmlspecialchars($method); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $status = $order['status'] ?? 'pending';
                                    $status_class = 'bg-info';
                                    if ($status == 'completed') $status_class = 'bg-success';
                                    if ($status == 'cancelled') $status_class = 'bg-danger';
                                    if ($status == 'processing') $status_class = 'bg-warning';
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </td>
                                <td><strong style="color: #00227c;">Rp <?php echo number_format($order['total_harga'] + ($order['biaya_admin'] ?? 0), 0, ',', '.'); ?></strong></td>
                                <td><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <a href="pesanan.php?view=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center" style="padding: 60px;">
                                    <i class="fas fa-inbox" style="font-size: 64px; color: #ccc; margin-bottom: 20px; display: block;"></i>
                                    <h5 style="color: #666; margin: 0;">Belum ada pesanan</h5>
                                    <p style="color: #999; margin: 5px 0 0 0;">Pesanan akan muncul di sini setelah pelanggan melakukan pembelian</p>
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
