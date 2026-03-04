<?php
/**
 * Enhanced Admin Dashboard with Security Improvements
 */

// Define admin access constant
define('ADMIN_ACCESS', true);

// Security and authentication
require_once 'config/security.php';
require_once 'config/database.php';

// Require admin access
requireAdmin();

// Rate limiting for dashboard access
if (!checkRateLimit('dashboard_access', 10, 60)) {
    http_response_code(429);
    die('Too many requests. Please try again later.');
}

// Log dashboard access
logAdminAction('Dashboard Access', 'User accessed admin dashboard');

// Enhanced database queries with prepared statements
try {
    $db = getDatabaseConnection();

    // Query untuk mendapatkan data pesanan per hari (7 hari terakhir)
    $stmt = mysqli_prepare($db, "SELECT DATE(created_at) as order_date, COUNT(*) as total 
                         FROM orders 
                         WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                         GROUP BY order_date
                         ORDER BY order_date ASC");
    mysqli_stmt_execute($stmt);
    $queryOrdersDaily = mysqli_stmt_get_result($stmt);

    // Siapkan array untuk 7 hari terakhir
    $dates = [];
    $orderCounts = [];
    $orderRevenue = [];

    // Isi dengan data default (0 pesanan) untuk 7 hari terakhir
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $formattedDate = date('d M', strtotime($date));
        $dates[] = $formattedDate;
        $orderCounts[$date] = 0;
        $orderRevenue[$date] = 0;
    }

    // Isi data dari database
    while ($row = mysqli_fetch_assoc($queryOrdersDaily)) {
        $date = $row['order_date'];
        $orderCounts[$date] = intval($row['total']);
    }

    // Query untuk mendapatkan revenue per hari
    $stmt = mysqli_prepare($db, "SELECT DATE(created_at) as order_date, SUM(total_harga) as revenue 
                         FROM orders 
                         WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                         GROUP BY order_date
                         ORDER BY order_date ASC");
    mysqli_stmt_execute($stmt);
    $queryRevenue = mysqli_stmt_get_result($stmt);

    // Isi data revenue dari database
    while ($row = mysqli_fetch_assoc($queryRevenue)) {
        $date = $row['order_date'];
        $orderRevenue[$date] = intval($row['revenue']);
    }

    // Ambil hanya nilai count dalam urutan yang sama dengan tanggal
    $ordersData = [];
    $revenueData = [];
    foreach (array_keys($orderCounts) as $date) {
        $ordersData[] = $orderCounts[$date];
        $revenueData[] = $orderRevenue[$date];
    }

    // Konversi ke format JSON untuk digunakan di JavaScript
    $datesJSON = json_encode($dates);
    $ordersDataJSON = json_encode($ordersData);
    $revenueDataJSON = json_encode($revenueData);

    // Enhanced statistics with error handling
    $stmt = mysqli_prepare($db, "SELECT COUNT(*) as total FROM orders");
    mysqli_stmt_execute($stmt);
    $total_orders = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];

    $stmt = mysqli_prepare($db, "SELECT COUNT(*) as total FROM barang");
    mysqli_stmt_execute($stmt);
    $total_products = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];

    $stmt = mysqli_prepare($db, "SELECT COUNT(*) as total FROM user WHERE level = 'user'");
    mysqli_stmt_execute($stmt);
    $total_users = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];

    $stmt = mysqli_prepare($db, "SELECT COALESCE(SUM(total_harga), 0) as total FROM orders");
    mysqli_stmt_execute($stmt);
    $total_revenue = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];

    // Recent orders with limit
    $stmt = mysqli_prepare($db, "SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
    mysqli_stmt_execute($stmt);
    $recent_orders = mysqli_stmt_get_result($stmt);

    // Quick stats untuk hari ini
    $stmt = mysqli_prepare($db, "SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = CURDATE()");
    mysqli_stmt_execute($stmt);
    $today_orders = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];

    $stmt = mysqli_prepare($db, "SELECT COALESCE(SUM(total_harga), 0) as total FROM orders WHERE DATE(created_at) = CURDATE()");
    mysqli_stmt_execute($stmt);
    $today_revenue = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];

    // Pending orders
    $stmt = mysqli_prepare($db, "SELECT COUNT(*) as total FROM orders WHERE status = 'pending' OR status IS NULL");
    mysqli_stmt_execute($stmt);
    $pending_orders = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];

} catch (Exception $e) {
    logAdminAction('Dashboard Error', $e->getMessage(), 'ERROR');
    $error_message = "Error loading dashboard data. Please try again.";

    // Set default values
    $total_orders = $total_products = $total_users = $total_revenue = 0;
    $today_orders = $today_revenue = $pending_orders = 0;
    $datesJSON = $ordersDataJSON = $revenueDataJSON = '[]';
}

$page_title = "Dashboard";
require_once 'include/admin_header.php';
?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Google Fonts -->
<link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;700;800&display=swap"
    rel="stylesheet">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    /* Global Variables */
    :root {
        --primary-color: #00227c;
        --secondary-color: #001a5e;
        --accent-color: #f48c06;
        --dark-blue: #00227c;
        --white: #ffffff;
        --orange: #f69e22;
        --light-bg: #f8fafc;
        --text-dark: #1e293b;
        --text-muted: #64748b;
        --radius-md: 12px;
        --radius-lg: 20px;
        --shadow-sm: 0 4px 6px rgba(0, 0, 0, 0.05);
        --shadow-md: 0 10px 25px rgba(0, 0, 0, 0.08);
        --shadow-lg: 0 20px 40px rgba(0, 0, 0, 0.12);
        --transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
        --info-color: #3b82f6;
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: var(--light-bg);
        color: var(--text-dark);
    }

    /* Enhanced Dashboard Styles */
    .dashboard-welcome {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: var(--white);
        padding: 40px;
        border-radius: var(--radius-lg);
        margin: 20px 0 30px;
        box-shadow: var(--shadow-md);
        position: relative;
        overflow: hidden;
    }

    .dashboard-welcome::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 60vh;
        height: 60vh;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 60%);
        border-radius: 50%;
        animation: float 8s ease-in-out infinite alternate;
    }

    .welcome-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        z-index: 1;
    }

    .welcome-text h2 {
        margin: 0 0 10px 0;
        font-size: 32px;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    .welcome-text p {
        margin: 0;
        opacity: 0.9;
        font-size: 16px;
        font-weight: 400;
    }

    .welcome-time {
        text-align: right;
        opacity: 0.8;
    }

    .dashboard-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 25px;
        margin: 30px 0;
    }

    .stat-card {
        background: var(--white);
        border-radius: var(--radius-lg);
        padding: 30px;
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 120px;
        height: 120px;
        background: var(--card-color, var(--primary-color));
        opacity: 0.05;
        border-radius: 50%;
        transform: translate(30px, -30px);
        transition: var(--transition);
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-lg);
    }

    .stat-card:hover::before {
        transform: translate(20px, -20px) scale(1.2);
        opacity: 0.08;
    }

    .stat-card.orders {
        --card-color: var(--primary-color);
    }

    .stat-card.products {
        --card-color: var(--success-color);
    }

    .stat-card.users {
        --card-color: var(--warning-color);
    }

    .stat-card.revenue {
        --card-color: var(--accent-color);
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 25px;
    }

    .stat-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: var(--card-color, var(--primary-color));
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .stat-trend {
        font-size: 13px;
        padding: 6px 14px;
        border-radius: 20px;
        background: rgba(16, 185, 129, 0.1);
        color: var(--success-color);
        font-weight: 700;
    }

    .stat-value {
        font-family: 'Outfit', sans-serif;
        font-size: 40px;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 8px;
        line-height: 1.1;
        letter-spacing: -1px;
    }

    .stat-label {
        color: #6b7280;
        font-size: 16px;
        font-weight: 500;
        margin-bottom: 8px;
    }

    .stat-change {
        font-size: 14px;
        color: var(--success-color);
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .chart-container {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        padding: 35px;
        margin: 30px 0;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .chart-title {
        font-family: 'Outfit', sans-serif;
        font-size: 24px;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
        letter-spacing: -0.5px;
    }

    .recent-orders {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        padding: 35px;
        margin: 30px 0;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .recent-orders h3 {
        margin-bottom: 30px;
        color: var(--text-dark);
        font-weight: 700;
        font-family: 'Outfit', sans-serif;
        font-size: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        letter-spacing: -0.5px;
    }

    .new-orders-alert {
        background: linear-gradient(135deg, var(--accent-color), var(--orange));
        color: var(--white);
        border-radius: var(--radius-lg);
        padding: 30px;
        margin: 25px 0;
        box-shadow: 0 10px 25px rgba(246, 158, 34, 0.3);
        position: relative;
        overflow: hidden;
    }

    .new-orders-alert::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
        0% {
            left: -100%;
        }

        100% {
            left: 100%;
        }
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    .new-orders-alert h4 {
        margin-bottom: 15px;
        font-weight: 700;
        font-size: 20px;
    }

    .new-orders-alert .btn {
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.3);
        color: var(--white);
        font-weight: 600;
        padding: 12px 24px;
        border-radius: 8px;
        transition: var(--transition);
    }

    .new-orders-alert .btn:hover {
        background: rgba(255, 255, 255, 0.3);
        color: var(--white);
        transform: translateY(-2px);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .dashboard-stats {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .stat-card {
            padding: 25px 20px;
        }

        .stat-value {
            font-size: 28px;
        }

        .welcome-content {
            flex-direction: column;
            text-align: center;
            gap: 20px;
        }

        .chart-header {
            flex-direction: column;
            align-items: stretch;
        }

        .chart-actions {
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .dashboard-stats {
            grid-template-columns: 1fr;
        }

        .stat-card {
            padding: 20px;
        }

        .chart-container,
        .recent-orders {
            padding: 20px;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1 class="page-title"><i class="fas fa-tachometer-alt"></i> Dashboard Admin</h1>
        <p class="page-subtitle">Selamat datang di panel admin Snack In</p>
    </div>
</div>

<div class="container-fluid">
    <!-- Navigation Buttons -->
    <div class="nav-buttons">
        <a href="index.php" class="nav-btn active">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="data_barang.php" class="nav-btn" onclick="showLoading()">
            <i class="fas fa-box"></i> Data Barang
        </a>
        <a href="kategori.php" class="nav-btn" onclick="showLoading()">
            <i class="fas fa-tags"></i> Kategori
        </a>
        <a href="pesanan.php" class="nav-btn" onclick="showLoading()">
            <i class="fas fa-shopping-cart"></i> Pesanan
        </a>
        <a href="user.php" class="nav-btn" onclick="showLoading()">
            <i class="fas fa-users"></i> User
        </a>
        <a href="history_admin.php" class="nav-btn" onclick="showLoading()">
            <i class="fas fa-history"></i> History
        </a>
    </div>

    <div class="content-container">
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Welcome Section -->
        <div class="dashboard-welcome">
            <div class="welcome-content">
                <div class="welcome-text">
                    <h2>Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>! 👋</h2>
                    <p>Berikut adalah ringkasan aktivitas toko Anda hari ini</p>
                </div>
                <div class="welcome-time">
                    <div style="font-size: 14px; margin-bottom: 5px;"><?php echo date('l, d F Y'); ?></div>
                </div>
            </div>
        </div>

        <!-- New Orders Alert -->
        <?php if ($pending_orders > 0): ?>
            <div class="new-orders-alert">
                <h4><i class="fas fa-bell"></i> Pesanan Baru!</h4>
                <p class="mb-3">
                    Anda memiliki <strong><?php echo $pending_orders; ?> pesanan baru</strong> yang perlu diproses segera.
                    <?php if ($today_orders > 0): ?>
                        Total hari ini: <strong><?php echo $today_orders; ?> pesanan</strong> dengan pendapatan <strong>Rp
                            <?php echo number_format($today_revenue, 0, ',', '.'); ?></strong>.
                    <?php endif; ?>
                </p>
                <a href="pesanan.php" class="btn" onclick="showLoading()">
                    <i class="fas fa-eye"></i> Lihat Pesanan
                </a>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="dashboard-stats">
            <div class="stat-card orders">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-trend">+<?php echo $today_orders; ?> hari ini</div>
                </div>
                <div class="stat-value"><?php echo number_format($total_orders); ?></div>
                <div class="stat-label">Total Pesanan</div>
                <div class="stat-change">
                    <i class="fas fa-arrow-up"></i> <?php echo $pending_orders; ?> pending
                </div>
            </div>

            <div class="stat-card products">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-trend">Aktif</div>
                </div>
                <div class="stat-value"><?php echo number_format($total_products); ?></div>
                <div class="stat-label">Total Produk</div>
                <div class="stat-change">
                    <i class="fas fa-check-circle"></i> Semua tersedia
                </div>
            </div>

            <div class="stat-card users">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-trend">Terdaftar</div>
                </div>
                <div class="stat-value"><?php echo number_format($total_users); ?></div>
                <div class="stat-label">Total User</div>
                <div class="stat-change">
                    <i class="fas fa-user-plus"></i> Pelanggan aktif
                </div>
            </div>

            <div class="stat-card revenue">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-trend">+Rp <?php echo number_format($today_revenue, 0, ',', '.'); ?></div>
                </div>
                <div class="stat-value">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></div>
                <div class="stat-label">Total Pendapatan</div>
                <div class="stat-change">
                    <i class="fas fa-chart-line"></i> Hari ini
                </div>
            </div>
        </div>

        <!-- Orders Chart Section -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Analisis Pesanan (7 Hari Terakhir)</h3>
                <div class="chart-actions">
                    <button class="chart-type-btn active" data-chart-type="bar">Bar</button>
                    <button class="chart-type-btn" data-chart-type="line">Line</button>
                    <button class="chart-type-btn" data-chart-type="revenue">Revenue</button>
                </div>
            </div>
            <canvas id="ordersChart" height="100"></canvas>
        </div>

        <!-- Recent Orders -->
        <div class="recent-orders">
            <h3><i class="fas fa-clock"></i> Pesanan Terbaru</h3>
            <div class="table-container">
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
                                Total</th>
                            <th
                                style="padding: 1rem; border-bottom: 2px solid rgba(0,0,0,0.05); text-align: left; font-weight: 700; color: var(--text-muted);">
                                Status</th>
                            <th
                                style="padding: 1rem; border-bottom: 2px solid rgba(0,0,0,0.05); text-align: left; font-weight: 700; color: var(--text-muted);">
                                Tanggal</th>
                            <th
                                style="padding: 1rem; border-bottom: 2px solid rgba(0,0,0,0.05); text-align: center; font-weight: 700; color: var(--text-muted);">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
                            <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid rgba(0,0,0,0.05); transition: var(--transition-fast);">
                                    <td style="padding: 1rem;"><strong
                                            style="color: var(--primary-color);">#<?php echo $order['id']; ?></strong></td>
                                    <td style="padding: 1rem; font-weight: 500;"><?php echo htmlspecialchars($order['nama']); ?>
                                    </td>
                                    <td style="padding: 1rem;"><strong style="font-family: 'Outfit', sans-serif;">Rp
                                            <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></strong></td>
                                    <td style="padding: 1rem;">
                                        <?php
                                        $status = $order['status'] ?? 'pending';

                                        // Make inline badge
                                        $bg = '#e2e8f0';
                                        $col = '#475569';
                                        if ($status == 'completed') {
                                            $bg = '#d1fae5';
                                            $col = '#059669';
                                        }
                                        if ($status == 'processing') {
                                            $bg = '#fef3c7';
                                            $col = '#d97706';
                                        }
                                        if ($status == 'cancelled') {
                                            $bg = '#fee2e2';
                                            $col = '#dc2626';
                                        }
                                        if ($status == 'pending') {
                                            $bg = '#e0e7ff';
                                            $col = '#4f46e5';
                                        }
                                        ?>
                                        <span
                                            style="background: <?php echo $bg; ?>; color: <?php echo $col; ?>; padding: 6px 14px; border-radius: 50px; font-size: 0.85rem; font-weight: 600; display: inline-block;">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 1rem; color: var(--text-muted); font-size: 0.95rem;">
                                        <?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <a href="pesanan.php?view=<?php echo $order['id']; ?>" class="btn btn-sm"
                                            style="display: inline-block; padding: 8px 16px; background: var(--primary-color); color: white; text-decoration: none; border-radius: var(--radius-md); font-weight: 600; font-size: 0.85rem; transition: var(--transition); box-shadow: 0 4px 6px rgba(0,34,124,0.2);"
                                            onclick="showLoading()">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center" style="padding: 60px 40px; text-align: center;">
                                    <div
                                        style="width: 80px; height: 80px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                                        <i class="fas fa-inbox" style="font-size: 32px; color: #94a3b8;"></i>
                                    </div>
                                    <h4
                                        style="color: var(--text-dark); font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 700; margin-bottom: 8px;">
                                        Belum ada pesanan</h4>
                                    <p style="color: var(--text-muted); margin: 0;">Saat ini belum ada data pesanan baru
                                        yang masuk ke sistem.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Enhanced JavaScript with security improvements
    document.addEventListener('DOMContentLoaded', function () {
        // Prevent multiple initializations
        if (window.chartInitialized) return;
        window.chartInitialized = true;

        // Data dari PHP dengan validation
        let dates, orderCounts, revenueData;
        try {
            dates = <?php echo $datesJSON; ?>;
            orderCounts = <?php echo $ordersDataJSON; ?>;
            revenueData = <?php echo $revenueDataJSON; ?>;

            // Validate data
            if (!Array.isArray(dates) || !Array.isArray(orderCounts) || !Array.isArray(revenueData)) {
                throw new Error('Invalid chart data');
            }
        } catch (e) {
            console.error('Chart data error:', e);
            dates = [];
            orderCounts = [];
            revenueData = [];
        }

        // Initialize chart only if canvas exists
        const chartCanvas = document.getElementById('ordersChart');
        if (!chartCanvas || dates.length === 0) {
            console.warn('Chart canvas not found or no data available');
            return;
        }

        // Create chart with enhanced security
        const ctx = chartCanvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 150);
        gradient.addColorStop(0, 'rgba(0, 34, 124, 0.7)');
        gradient.addColorStop(1, 'rgba(0, 34, 124, 0.1)');

        // Chart configuration
        const chartConfig = {
            type: 'bar',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Jumlah Pesanan',
                    data: orderCounts,
                    backgroundColor: gradient,
                    borderColor: 'rgba(0, 34, 124, 1)',
                    borderWidth: 2,
                    borderRadius: 8,
                    maxBarThickness: 50
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 3,
                animation: {
                    duration: 800,
                    easing: 'easeOutQuart'
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            font: {
                                family: 'Poppins',
                                size: 12
                            },
                            color: '#6b7280'
                        },
                        grid: {
                            color: 'rgba(200, 200, 200, 0.2)',
                            drawBorder: false
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                family: 'Poppins',
                                size: 12
                            },
                            color: '#6b7280'
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                family: 'Poppins',
                                size: 13,
                                weight: '600'
                            },
                            boxWidth: 12,
                            padding: 15,
                            color: '#374151'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            family: 'Poppins',
                            size: 14,
                            weight: '600'
                        },
                        bodyFont: {
                            family: 'Poppins',
                            size: 13
                        },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            title: function (tooltipItems) {
                                return 'Tanggal: ' + tooltipItems[0].label;
                            },
                            label: function (context) {
                                if (context.dataset.label === 'Jumlah Pesanan') {
                                    return 'Pesanan: ' + context.raw;
                                } else {
                                    return 'Pendapatan: Rp ' + formatNumber(context.raw);
                                }
                            }
                        }
                    }
                }
            }
        };

        // Create chart
        const ordersChart = new Chart(ctx, chartConfig);

        // Chart type buttons event listeners
        document.querySelectorAll('.chart-type-btn').forEach(button => {
            button.addEventListener('click', function () {
                // Remove active class from all buttons
                document.querySelectorAll('.chart-type-btn').forEach(btn => {
                    btn.classList.remove('active');
                });

                // Add active class to clicked button
                this.classList.add('active');

                // Change chart type
                const chartType = this.getAttribute('data-chart-type');

                try {
                    if (chartType === 'revenue') {
                        // Show revenue data
                        ordersChart.data.datasets[0].label = 'Pendapatan (Rp)';
                        ordersChart.data.datasets[0].data = revenueData;
                        ordersChart.options.scales.y.ticks.callback = function (value) {
                            return 'Rp ' + formatNumber(value);
                        };
                    } else {
                        // Show orders data
                        ordersChart.data.datasets[0].label = 'Jumlah Pesanan';
                        ordersChart.data.datasets[0].data = orderCounts;
                        ordersChart.options.scales.y.ticks.callback = function (value) {
                            return value;
                        };

                        // Change chart type (bar or line)
                        ordersChart.config.type = chartType;

                        if (chartType === 'line') {
                            // Line chart configuration
                            ordersChart.data.datasets[0].fill = true;
                            ordersChart.data.datasets[0].tension = 0.4;
                            ordersChart.data.datasets[0].pointBackgroundColor = 'rgba(0, 34, 124, 1)';
                            ordersChart.data.datasets[0].pointBorderColor = '#ffffff';
                            ordersChart.data.datasets[0].pointBorderWidth = 2;
                            ordersChart.data.datasets[0].pointRadius = 5;
                        } else {
                            // Bar chart configuration
                            ordersChart.data.datasets[0].fill = false;
                            ordersChart.data.datasets[0].tension = 0;
                        }
                    }

                    // Update chart
                    ordersChart.update('active');
                } catch (error) {
                    console.error('Chart update error:', error);
                }
            });
        });

        // Format number with thousand separators
        function formatNumber(number) {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
    });
</script>

<?php require_once 'include/admin_footer.php'; ?>