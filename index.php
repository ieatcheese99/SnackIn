<?php
// Cek akses admin
require_once 'admin_check.php';
requireAdmin();

require "config/database.php";

// Query untuk mendapatkan data pesanan per hari (7 hari terakhir)
$query = "SELECT DATE(created_at) as order_date, COUNT(*) as total 
         FROM orders 
         WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY order_date
        ORDER BY order_date ASC";
$queryOrdersDaily = mysqli_query($db, $query);

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
if ($queryOrdersDaily) {
    while ($row = mysqli_fetch_assoc($queryOrdersDaily)) {
        $date = $row['order_date'];
        $orderCounts[$date] = intval($row['total']);
    }
}

// Query untuk mendapatkan revenue per hari
$revenueQuery = "SELECT DATE(created_at) as order_date, SUM(total_harga) as revenue 
                FROM orders 
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY order_date
                ORDER BY order_date ASC";
$queryRevenue = mysqli_query($db, $revenueQuery);

// Isi data revenue dari database
if ($queryRevenue) {
    while ($row = mysqli_fetch_assoc($queryRevenue)) {
        $date = $row['order_date'];
        $orderRevenue[$date] = intval($row['revenue']);
    }
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

// Statistik dashboard
$total_orders = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM orders"))['total'];
$total_products = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM barang"))['total'];
$total_users = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM user WHERE level = 'user'"))['total'];
$total_revenue = mysqli_fetch_assoc(mysqli_query($db, "SELECT SUM(total_harga) as total FROM orders"))['total'] ?? 0;

// Recent orders
$recent_orders = mysqli_query($db, "SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
?>

<!doctype html>
<html lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#000000">
    <title>Admin Dashboard - Snack In</title>
    <meta name="description" content="Snack In Admin Dashboard">
    <link rel="icon" type="image/png" href="assets/img/favicon.png" sizes="32x32">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/icon/192x192.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="manifest" href="__manifest.json">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Styling untuk chart container */
        .chart-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            padding: 15px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .chart-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        .chart-actions {
            display: flex;
            gap: 5px;
        }

        .chart-type-btn {
            background: #f5f5f5;
            border: none;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .chart-type-btn.active {
            background: #00227c;
            color: white;
        }

        .chart-stats {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .stat-card {
            flex: 1;
            background: #f9f9f9;
            border-radius: 8px;
            padding: 8px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-value {
            font-size: 1.2rem;
            font-weight: 700;
            color: #00227c;
            margin-bottom: 2px;
        }

        .stat-label {
            font-size: 0.7rem;
            color: #666;
        }

        /* Recent Orders Table */
        .recent-orders {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 20px;
        }

        .recent-orders h6 {
            margin-bottom: 15px;
            color: #333;
            font-weight: 600;
        }

        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background-color: #00227c;
            color: white;
            border: none;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 12px 8px;
        }

        .table td {
            padding: 10px 8px;
            font-size: 0.8rem;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .badge {
            font-size: 0.7rem;
            padding: 4px 8px;
        }

        .btn-sm {
            padding: 4px 8px;
            font-size: 0.7rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .chart-stats {
                flex-direction: column;
                gap: 10px;
            }
            
            .table {
                font-size: 0.7rem;
            }
            
            .table th,
            .table td {
                padding: 8px 4px;
            }
        }
    </style>
</head>

<body>
    <!-- loader -->
    <div id="loader">
        <img src="assets/img/loading-icon.png" alt="icon" class="loading-icon">
    </div>
    <!-- * loader -->

    <!-- App Header -->
    <div class="appHeader bg-primary text-light">
        <div class="left">
            <a href="#" class="headerButton" data-bs-toggle="modal" data-bs-target="#sidebarPanel">
                <ion-icon name="menu-outline"></ion-icon>
            </a>
        </div>
        <div class="pageTitle">
            <img src="assets/img/logo.png" alt="logo" class="logo">
        </div>
        <div class="right">
            <a href="user_ui.php" class="headerButton">
                <ion-icon class="icon" name="globe-outline"></ion-icon>
            </a>
            <a href="logout.php" class="headerButton">
                <ion-icon class="icon" name="log-out-outline"></ion-icon>
            </a>
        </div>
    </div>
    <!-- * App Header -->

    <!-- App Capsule -->
    <div id="appCapsule">
        <!-- Wallet Card -->
        <div class="section wallet-card-section pt-1">
            <div class="wallet-card">
                <!-- Balance -->
                <div class="balance">
                    <div class="left">
                        <span class="title">Admin Dashboard</span>
                        <h1 class="total"><?php echo $_SESSION['username']; ?></h1>
                    </div>
                    <div class="right">
                        <span class="badge badge-success">Online</span>
                    </div>
                </div>
                <!-- * Balance -->
                <!-- Wallet Footer -->
                <div class="wallet-footer">
                    <div class="item">
                        <a href="data_barang.php" style="text-decoration: none;">
                            <div class="icon-wrapper bg-danger">
                                <ion-icon name="pricetags-outline"></ion-icon>
                            </div>
                            <strong>Data Barang</strong>
                        </a>
                    </div>
                    <div class="item">
                        <a href="kategori.php" style="text-decoration: none;">
                            <div class="icon-wrapper bg-warning">
                                <ion-icon name="list-outline"></ion-icon>
                            </div>
                            <strong>Kategori</strong>
                        </a>
                    </div>
                    <div class="item">
                        <a href="pesanan.php" style="text-decoration: none;">
                            <div class="icon-wrapper bg-primary">
                                <ion-icon name="bag-outline"></ion-icon>
                            </div>
                            <strong>Pesanan</strong>
                        </a>
                    </div>
                    <div class="item">
                        <a href="user.php" style="text-decoration: none;">
                            <div class="icon-wrapper bg-success">
                                <ion-icon name="person-circle-outline"></ion-icon>
                            </div>
                            <strong>User</strong>
                        </a>
                    </div>
                    <div class="item">
                        <a href="history_admin.php" style="text-decoration: none;">
                            <div class="icon-wrapper bg-info">
                                <ion-icon name="time-outline"></ion-icon>
                            </div>
                            <strong>History</strong>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="section mt-2">
            <div class="row">
                <div class="col-6 mb-2">
                    <div class="stat-box">
                        <div class="title">Total Pesanan</div>
                        <div class="value text-primary"><?php echo $total_orders; ?></div>
                    </div>
                </div>
                <div class="col-6 mb-2">
                    <div class="stat-box">
                        <div class="title">Total Produk</div>
                        <div class="value text-success"><?php echo $total_products; ?></div>
                    </div>
                </div>
                <div class="col-6 mb-2">
                    <div class="stat-box">
                        <div class="title">Total User</div>
                        <div class="value text-warning"><?php echo $total_users; ?></div>
                    </div>
                </div>
                <div class="col-6 mb-2">
                    <div class="stat-box">
                        <div class="title">Pendapatan</div>
                        <div class="value text-danger">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Chart Section -->
        <div class="section mt-2">
            <div class="chart-container">
                <div class="chart-header">
                    <h6 class="chart-title">Analisis Pesanan (7 Hari Terakhir)</h6>
                    <div class="chart-actions">
                        <button class="chart-type-btn active" data-chart-type="bar">Bar</button>
                        <button class="chart-type-btn" data-chart-type="line">Line</button>
                        <button class="chart-type-btn" data-chart-type="revenue">Revenue</button>
                    </div>
                </div>
                <canvas id="ordersChart" height="150"></canvas>

                <div class="chart-stats">
                    <div class="stat-card">
                        <div class="stat-value" id="total-orders"><?php echo $total_orders; ?></div>
                        <div class="stat-label">Total Pesanan</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" id="total-revenue">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></div>
                        <div class="stat-label">Total Pendapatan</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" id="avg-order">Rp <?php echo $total_orders > 0 ? number_format($total_revenue / $total_orders, 0, ',', '.') : '0'; ?></div>
                        <div class="stat-label">Rata-rata Pesanan</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="section">
            <div class="recent-orders">
                <h6><i class="fas fa-clock"></i> Pesanan Terbaru</h6>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_orders && mysqli_num_rows($recent_orders) > 0): ?>
                                <?php while ($order = mysqli_fetch_assoc($recent_orders)): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['nama']); ?></td>
                                    <td>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo ucfirst($order['status'] ?? 'pending'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="pesanan.php?view=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada pesanan</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- * App Capsule -->

    <!-- App Bottom Menu -->
    <div class="appBottomMenu">
        <a href="index.php" class="item active">
            <div class="col">
                <ion-icon name="pie-chart-outline"></ion-icon>
                <strong>Dashboard</strong>
            </div>
        </a>
        <a href="data_barang.php" class="item">
            <div class="col">
                <ion-icon name="cube-outline"></ion-icon>
                <strong>Produk</strong>
            </div>
        </a>
        <a href="pesanan.php" class="item">
            <div class="col">
                <ion-icon name="bag-outline"></ion-icon>
                <strong>Pesanan</strong>
            </div>
        </a>
        <a href="user.php" class="item">
            <div class="col">
                <ion-icon name="people-outline"></ion-icon>
                <strong>User</strong>
            </div>
        </a>
        <a href="logout.php" class="item">
            <div class="col">
                <ion-icon name="log-out-outline"></ion-icon>
                <strong>Logout</strong>
            </div>
        </a>
    </div>
    <!-- * App Bottom Menu -->

    <!-- App Sidebar -->
    <div class="modal fade panelModal panelModalLeft" id="sidebarPanel" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <!-- profile box -->
                    <div class="profileBox pt-2 pb-2">
                        <div class="image-wrapper">
                            <img src="assets/img/sample/avatar/avatar1.jpg" alt="image" class="imaged  w36">
                        </div>
                        <div class="in">
                            <strong><?php echo $_SESSION['username']; ?></strong>
                            <div class="text-muted">Administrator</div>
                        </div>
                        <a href="#" class="btn btn-link btn-icon sidebar-close" data-bs-dismiss="modal">
                            <ion-icon name="close-outline"></ion-icon>
                        </a>
                    </div>
                    <!-- * profile box -->
                    <!-- menu -->
                    <div class="listview-title mt-1">Menu</div>
                    <ul class="listview flush transparent no-line image-listview">
                        <li>
                            <a href="index.php" class="item">
                                <div class="icon-box bg-primary">
                                    <ion-icon name="pie-chart-outline"></ion-icon>
                                </div>
                                <div class="in">
                                    Dashboard
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="data_barang.php" class="item">
                                <div class="icon-box bg-danger">
                                    <ion-icon name="cube-outline"></ion-icon>
                                </div>
                                <div class="in">
                                    Data Barang
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="kategori.php" class="item">
                                <div class="icon-box bg-warning">
                                    <ion-icon name="list-outline"></ion-icon>
                                </div>
                                <div class="in">
                                    Kategori
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="pesanan.php" class="item">
                                <div class="icon-box bg-success">
                                    <ion-icon name="bag-outline"></ion-icon>
                                </div>
                                <div class="in">
                                    Pesanan
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="user.php" class="item">
                                <div class="icon-box bg-info">
                                    <ion-icon name="people-outline"></ion-icon>
                                </div>
                                <div class="in">
                                    User Management
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="history_admin.php" class="item">
                                <div class="icon-box bg-secondary">
                                    <ion-icon name="time-outline"></ion-icon>
                                </div>
                                <div class="in">
                                    History
                                </div>
                            </a>
                        </li>
                    </ul>
                    <!-- * menu -->

                    <!-- others -->
                    <div class="listview-title mt-1">Others</div>
                    <ul class="listview flush transparent no-line image-listview">
                        <li>
                            <a href="user_ui.php" class="item">
                                <div class="icon-box bg-primary">
                                    <ion-icon name="globe-outline"></ion-icon>
                                </div>
                                <div class="in">
                                    Lihat Website
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="logout.php" class="item">
                                <div class="icon-box bg-danger">
                                    <ion-icon name="log-out-outline"></ion-icon>
                                </div>
                                <div class="in">
                                    Logout
                                </div>
                            </a>
                        </li>
                    </ul>
                    <!-- * others -->
                </div>
            </div>
        </div>
    </div>
    <!-- * App Sidebar -->

    <!-- ========= JS Files =========  -->
    <!-- Bootstrap -->
    <script src="assets/js/lib/bootstrap.bundle.min.js"></script>
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <!-- Splide -->
    <script src="assets/js/plugins/splide/splide.min.js"></script>
    <!-- Base Js File -->
    <script src="assets/js/base.js"></script>

    <!-- Chart.js Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Prevent multiple initializations
            if (window.chartInitialized) return;
            window.chartInitialized = true;

            // Data dari PHP
            const dates = <?php echo $datesJSON; ?>;
            const orderCounts = <?php echo $ordersDataJSON; ?>;
            const revenueData = <?php echo $revenueDataJSON; ?>;

            // Buat gradien untuk chart
            const ctx = document.getElementById('ordersChart').getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 0, 150);
            gradient.addColorStop(0, 'rgba(0, 34, 124, 0.7)');
            gradient.addColorStop(1, 'rgba(0, 34, 124, 0.1)');

            // Konfigurasi chart
            const chartConfig = {
                type: 'bar',
                data: {
                    labels: dates,
                    datasets: [{
                        label: 'Jumlah Pesanan',
                        data: orderCounts,
                        backgroundColor: gradient,
                        borderColor: 'rgba(0, 34, 124, 1)',
                        borderWidth: 1,
                        borderRadius: 5,
                        maxBarThickness: 50
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 2,
                    animation: {
                        duration: 800,
                        easing: 'easeOutQuart'
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                font: {
                                    family: 'Roboto',
                                    size: 9
                                }
                            },
                            grid: {
                                color: 'rgba(200, 200, 200, 0.2)'
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    family: 'Roboto',
                                    size: 9
                                }
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
                                    family: 'Roboto',
                                    size: 10,
                                    weight: 'bold'
                                },
                                boxWidth: 8,
                                padding: 6
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(0, 0, 0, 0.7)',
                            titleFont: {
                                family: 'Roboto',
                                size: 11
                            },
                            bodyFont: {
                                family: 'Roboto',
                                size: 10
                            },
                            padding: 8,
                            cornerRadius: 4,
                            callbacks: {
                                title: function(tooltipItems) {
                                    return 'Tanggal: ' + tooltipItems[0].label;
                                },
                                label: function(context) {
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

            // Buat chart
            const ordersChart = new Chart(ctx, chartConfig);

            // Event listener untuk tombol tipe chart
            document.querySelectorAll('.chart-type-btn').forEach(button => {
                button.addEventListener('click', function() {
                    // Hapus class active dari semua tombol
                    document.querySelectorAll('.chart-type-btn').forEach(btn => {
                        btn.classList.remove('active');
                    });

                    // Tambahkan class active ke tombol yang diklik
                    this.classList.add('active');

                    // Ubah tipe chart berdasarkan tombol yang diklik
                    const chartType = this.getAttribute('data-chart-type');

                    if (chartType === 'revenue') {
                        // Tampilkan data revenue
                        ordersChart.data.datasets[0].label = 'Pendapatan (Rp)';
                        ordersChart.data.datasets[0].data = revenueData;
                        ordersChart.options.scales.y.ticks.callback = function(value) {
                            return 'Rp ' + formatNumber(value);
                        };
                    } else {
                        // Tampilkan data pesanan
                        ordersChart.data.datasets[0].label = 'Jumlah Pesanan';
                        ordersChart.data.datasets[0].data = orderCounts;
                        ordersChart.options.scales.y.ticks.callback = function(value) {
                            return value;
                        };

                        // Ubah tipe chart (bar atau line)
                        ordersChart.config.type = chartType;

                        if (chartType === 'line') {
                            // Konfigurasi khusus untuk line chart
                            ordersChart.data.datasets[0].fill = true;
                            ordersChart.data.datasets[0].tension = 0.4;
                        } else {
                            // Konfigurasi khusus untuk bar chart
                            ordersChart.data.datasets[0].fill = false;
                            ordersChart.data.datasets[0].tension = 0;
                        }
                    }

                    // Update chart
                    ordersChart.update();
                });
            });

            // Fungsi untuk format angka dengan pemisah ribuan
            function formatNumber(number) {
                return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
        });

        // Add to Home with 2 seconds delay.
        AddtoHome("2000", "once");
    </script>
</body>

</html>
