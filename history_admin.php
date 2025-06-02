<?php
// Define admin access constant
define('ADMIN_ACCESS', true);

// Cek akses admin
require_once 'config/security.php';
requireAdmin();

require_once "config/database.php";

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

// Get all completed/delivered orders for history (no filters)
if (!$view_order) {
    $history_query = "SELECT o.* FROM orders o 
                     WHERE o.status IN ('completed', 'delivered')
                     ORDER BY o.created_at DESC";
    $history_result = mysqli_query($db, $history_query);
}

$page_title = "History Pesanan";
include 'include/admin_header.php';
?>

<style>
    .history-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }

    .stat-card {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        border-left: 4px solid #00227c;
    }

    .stat-number {
        font-size: 28px;
        font-weight: 700;
        color: #00227c;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 14px;
        color: #666;
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

    @media (max-width: 768px) {
        .history-stats {
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
            <i class="fas fa-history"></i> 
            <?php echo $view_order ? "Detail Pesanan #" . $view_order['id'] : "History Pesanan"; ?>
        </h1>
        <p class="page-subtitle">
            <?php echo $view_order ? "Detail lengkap pesanan yang telah selesai" : "Riwayat pesanan yang telah selesai"; ?>
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
        <a href="pesanan.php" class="nav-btn">
            <i class="fas fa-shopping-cart"></i> Pesanan
        </a>
        <a href="user.php" class="nav-btn">
            <i class="fas fa-users"></i> User
        </a>
        <a href="history_admin.php" class="nav-btn active">
            <i class="fas fa-history"></i> History
        </a>
    </div>

    <?php if ($view_order): ?>
        <!-- Detail View -->
        <div class="detail-container">
            <div class="detail-header">
                <h3><i class="fas fa-info-circle"></i> Detail Pesanan #<?php echo $view_order['id']; ?></h3>
                <a href="history_admin.php" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="detail-body">
                <div class="row">
                    <div class="col-md-6">
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
                                        if (strpos(strtolower($method), 'cod') !== false) $badge_class = 'bg-warning';
                                        if (strpos(strtolower($method), 'transfer') !== false) $badge_class = 'bg-info';
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo htmlspecialchars($method); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge bg-success">
                                            <?php echo ucfirst($view_order['status'] ?? 'completed'); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Total</th>
                                    <td><strong style="color: #00227c;">Rp <?php echo number_format($view_order['total_harga'], 0, ',', '.'); ?></strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
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
                    <div class="total-amount">
                        Total Pesanan: Rp <?php echo number_format($view_order['total_harga'], 0, ',', '.'); ?>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="history_admin.php" class="btn btn-secondary">
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
            <!-- History Statistics -->
            <div class="history-stats">
                <?php
                $total_completed = mysqli_num_rows($history_result);
                $total_revenue = 0;
                $today_orders = 0;
                $this_month_orders = 0;
                
                // Calculate statistics
                mysqli_data_seek($history_result, 0);
                while ($order = mysqli_fetch_assoc($history_result)) {
                    $total_revenue += $order['total_harga'];
                    
                    if (date('Y-m-d', strtotime($order['created_at'])) == date('Y-m-d')) {
                        $today_orders++;
                    }
                    
                    if (date('Y-m', strtotime($order['created_at'])) == date('Y-m')) {
                        $this_month_orders++;
                    }
                }
                ?>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_completed; ?></div>
                    <div class="stat-label">Total Pesanan Selesai</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></div>
                    <div class="stat-label">Total Pendapatan</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $today_orders; ?></div>
                    <div class="stat-label">Pesanan Hari Ini</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $this_month_orders; ?></div>
                    <div class="stat-label">Pesanan Bulan Ini</div>
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
                        mysqli_data_seek($history_result, 0);
                        if (mysqli_num_rows($history_result) > 0): 
                        ?>
                            <?php while ($order = mysqli_fetch_assoc($history_result)): ?>
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
                                    if (strpos(strtolower($method), 'cod') !== false) $badge_class = 'bg-warning';
                                    if (strpos(strtolower($method), 'transfer') !== false) $badge_class = 'bg-info';
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo htmlspecialchars($method); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-success">
                                        <?php echo ucfirst($order['status'] ?? 'completed'); ?>
                                    </span>
                                </td>
                                <td><strong style="color: #00227c;">Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></strong></td>
                                <td><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <a href="history_admin.php?view=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center" style="padding: 40px;">
                                    <i class="fas fa-history" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                                    <p style="color: #666; margin: 0;">Belum ada pesanan yang selesai</p>
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
