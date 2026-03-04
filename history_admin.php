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

    .history-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .stat-card {
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

    .stat-card::before {
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

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
    }

    .stat-card:hover::before {
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

    /* Animation */
    .content-animate {
        animation: fadeInUp 0.7s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .animate-item {
        animation: fadeIn 0.5s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
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
                                        if (strpos(strtolower($method), 'cod') !== false)
                                            $badge_class = 'bg-warning';
                                        if (strpos(strtolower($method), 'transfer') !== false)
                                            $badge_class = 'bg-info';
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
                                    <td><strong style="color: #00227c;">Rp
                                            <?php echo number_format($view_order['total_harga'], 0, ',', '.'); ?></strong>
                                    </td>
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
        <div class="content-container content-animate">
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
                <div class="stat-card animate-item">
                    <div class="stat-number"><?php echo $total_completed; ?></div>
                    <div class="stat-label">Total Pesanan Selesai</div>
                </div>
                <div class="stat-card animate-item">
                    <div class="stat-number">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></div>
                    <div class="stat-label">Total Pendapatan</div>
                </div>
                <div class="stat-card animate-item">
                    <div class="stat-number"><?php echo $today_orders; ?></div>
                    <div class="stat-label">Pesanan Hari Ini</div>
                </div>
                <div class="stat-card animate-item">
                    <div class="stat-number"><?php echo $this_month_orders; ?></div>
                    <div class="stat-label">Pesanan Bulan Ini</div>
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
                        mysqli_data_seek($history_result, 0);
                        if (mysqli_num_rows($history_result) > 0):
                            ?>
                            <?php while ($order = mysqli_fetch_assoc($history_result)): ?>
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
                                        if (strpos(strtolower($method), 'cod') !== false) {
                                            $bg_pay = '#fef3c7';
                                            $col_pay = '#d97706';
                                        }
                                        if (strpos(strtolower($method), 'transfer') !== false) {
                                            $bg_pay = '#e0f2fe';
                                            $col_pay = '#0ea5e9';
                                        }
                                        ?>
                                        <span
                                            style="background: <?php echo $bg_pay; ?>; color: <?php echo $col_pay; ?>; padding: 6px 12px; border-radius: 50px; font-size: 0.8rem; font-weight: 600; display: inline-block;">
                                            <?php echo htmlspecialchars($method); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 1rem;">
                                        <span
                                            style="background: #d1fae5; color: #059669; padding: 6px 12px; border-radius: 50px; font-size: 0.8rem; font-weight: 600; display: inline-block;">
                                            <?php echo ucfirst($order['status'] ?? 'completed'); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 1rem;"><strong style="font-family: 'Outfit', sans-serif;">Rp
                                            <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></strong></td>
                                    <td style="padding: 1rem; color: var(--text-muted); font-size: 0.9rem;">
                                        <?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?>
                                    </td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <a href="history_admin.php?view=<?php echo $order['id']; ?>" class="btn btn-sm"
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
                                        <i class="fas fa-history" style="font-size: 32px; color: #94a3b8;"></i>
                                    </div>
                                    <h4
                                        style="color: var(--text-dark); font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 700; margin-bottom: 8px;">
                                        Belum ada pesanan selesai</h4>
                                    <p style="color: var(--text-muted); margin: 0;">Riwayat pesanan yang sudah selesai atau
                                        terkirim akan muncul di sini.</p>
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