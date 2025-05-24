<?php
require "config/database.php";
$queryProduk = mysqli_query($db, "SELECT id, nama, harga, gambar, deskripsi FROM barang LIMIT 6");

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
$orderRevenue = []; // Tambahkan array untuk revenue

// Isi dengan data default (0 pesanan) untuk 7 hari terakhir
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $formattedDate = date('d M', strtotime($date)); // Format: 01 Jan
    $dates[] = $formattedDate;
    $orderCounts[$date] = 0;
    $orderRevenue[$date] = 0; // Default revenue 0
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
?>

<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#000000">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" integrity="sha384-dpuaG1suU0eT09tx5plTaGMLBsfDLzUCCUXOY2j/LSvXYuG6Bqs43ALlhIqAJVRb" crossorigin="anonymous">
    <title>Finapp</title>
    <style>
        .produk-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .produk-card {
            width: 250px;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
        }

        .produk-card img {
            width: 100%;
            /* Sesuaikan dengan lebar card */
            height: 200px;
            /* Atur tinggi agar seragam */
            object-fit: cover;
            /* Memotong gambar agar tetap proporsional */
            border-radius: 8px;
            /* Bikin sudutnya lebih halus */
        }

        /* Styling untuk chart container */
        .chart-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            padding: 15px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
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

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .chart-stats {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
    <!--====== Google Font ======-->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800" rel="stylesheet">
    <!--====== Vendor Css ======-->
    <link rel="stylesheet" href="css/vendor.css">
    <!--====== Utility-Spacing ======-->
    <link rel="stylesheet" href="css/utility.css">
    <!--====== App ======-->
    <link rel="stylesheet" href="css/app.css">
    <meta name="description" content="Finapp HTML Mobile Template">
    <meta name="keywords"
        content="bootstrap, wallet, banking, fintech mobile template, cordova, phonegap, mobile, html, responsive" />
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="manifest" href="__manifest.json">
    <link rel="apple-touch-icon" href="assets/img/Modern Colorful Company Profile Presentation.png">
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/Modern Colorful Company Profile Presentation.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/templatemo.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <!-- Load fonts style after rendering the layout styles -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <a href="include/cart.php" class="headerButton">
                <ion-icon name="cart-outline"></ion-icon>
                <span class="badge badge-danger cart-count">0</span>
            </a>
            <script>
                $(document).ready(function() {
                    function updateCartCount() {
                        $.ajax({
                            url: "cart_action.php",
                            type: "POST",
                            data: {
                                action: "count"
                            },
                            success: function(count) {
                                $(".cart-count").text(count);
                            }
                        });
                    }
                    updateCartCount();
                });
            </script>
            <a href="app-notifications.html" class="headerButton">
                <ion-icon class="icon" name="notifications-outline"></ion-icon>
                <span class="badge badge-danger">4</span>
            </a>
            <a href="app-settings.html" class="headerButton">
                <img src="assets/img/sample/avatar/avatar1.jpg" alt="image" class="imaged w32">
                <span class="badge badge-danger">6</span>
            </a>
            <a href="logout.php" class="headerButton">
                <ion-icon name="log-out-outline"></ion-icon>
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
                        <span class="title">Total Balance</span>
                        <h1 class="total">Muhammad Zahir Siraj</h1>
                    </div>
                    <div class="right">
                        <a href="#" class="button" data-bs-toggle="modal" data-bs-target="#depositActionSheet">
                            <ion-icon name="add-outline"></ion-icon>
                        </a>
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
                            <div class="icon-wrapper">
                                <ion-icon name="list-outline"></ion-icon>
                            </div>
                            <strong>Kategori</strong>
                        </a>
                    </div>
                    <div class="item">
                        <a href="pesanan.php" style="text-decoration: none;">
                            <div class="icon-wrapper bg-primary"> <!-- Warna biru -->
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
                            <div class="icon-wrapper bg-success">
                                <ion-icon name="time-outline"></ion-icon>
                            </div>
                            <strong>History</strong>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Chart Section -->
        <div class="section mt-4">
            <div class="chart-container">
                <div class="chart-header">
                    <h6 class="chart-title">Analisis Pesanan</h6>
                    <div class="chart-actions">
                        <button class="chart-type-btn active" data-chart-type="bar">Bar</button>
                        <button class="chart-type-btn" data-chart-type="line">Line</button>
                        <button class="chart-type-btn" data-chart-type="revenue">Revenue</button>
                    </div>
                </div>
                <canvas id="ordersChart" height="150"></canvas>

                <div class="chart-stats">
                    <div class="stat-card">
                        <div class="stat-value" id="total-orders">0</div>
                        <div class="stat-label">Total Pesanan</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" id="total-revenue">Rp 0</div>
                        <div class="stat-label">Total Pendapatan</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" id="avg-order">Rp 0</div>
                        <div class="stat-label">Rata-rata Pesanan</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exchange Action Sheet -->
        <div class="modal fade action-sheet" id="exchangeActionSheet" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Exchange Money</h5>
                    </div>
                    <div class="modal-body">
                        <div class="action-sheet-content">
                            <form>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group basic">
                                            <div class="input-wrapper">
                                                <label class="label" for="currency1">From</label>
                                                <select class="form-control custom-select" id="currency1">
                                                    <option value="1">EUR</option>
                                                    <option value="2">USD</option>
                                                    <option value="3">AUD</option>
                                                    <option value="4">CAD</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group basic">
                                            <div class="input-wrapper">
                                                <label class="label" for="currency2">To</label>
                                                <select class="form-control custom-select" id="currency2">
                                                    <option value="1">USD</option>
                                                    <option value="1">EUR</option>
                                                    <option value="2">USA</option>
                                                    <option value="3">CAD</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group basic">
                                    <label class="label">Enter Amount</label>
                                    <div class="input-group mb-2">
                                        <span class="input-group-text" id="basic-addon2">$</span>
                                        <input type="text" class="form-control" placeholder="Enter an amount"
                                            value="100">
                                    </div>
                                </div>
                                <div class="form-group basic">
                                    <button type="button" class="btn btn-primary btn-block btn-lg"
                                        data-bs-dismiss="modal">Exchange</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- * Exchange Action Sheet -->
        <!-- Stats -->
        <!-- * Stats -->
        <!-- Transactions -->
        <!-- * Transactions -->
        <!-- my cards -->
        <!-- * my cards -->
        <!-- Send Money -->
        <!-- * Send Money -->
        <!-- Monthly Bills -->
        <!-- * Monthly Bills -->
        <!-- Saving Goals -->
        <!-- * Saving Goals -->
        <!-- News -->
        <!-- * News -->
        <!-- app footer -->
        <!-- * app footer -->
    </div>
    <!-- * App Capsule -->
    <?php include "include/content.php" ?>
    <?php include "include/footer.php" ?>
    <!-- App Bottom Menu -->
    <?php include "include/bottom_menu.php" ?>
    <!-- * App Bottom Menu -->
    <!-- App Sidebar -->
    <?php include "include/sidebar.php" ?>
    <!-- * App Sidebar -->
    <!-- iOS Add to Home Action Sheet -->
    <?php include "include/ios_home.php" ?>
    <!-- * iOS Add to Home Action Sheet -->
    <!-- Android Add to Home Action Sheet -->
    <?php include "include/android_home.php" ?>
    <!-- * Android Add to Home Action Sheet -->
    +
    <!-- ========= JS Files =========  -->
    <!-- Bootstrap -->
    <script src="assets/js/lib/bootstrap.bundle.min.js"></script>
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <!-- Splide -->
    <script src="assets/js/plugins/splide/splide.min.js"></script>
    <!-- Base Js File -->
    <script src="assets/js/base.js"></script>
    <script>
        // Add to Home with 2 seconds delay.
        AddtoHome("2000", "once");
    </script>
    <!-- Start Script -->
    <script src="assets/js/jquery-1.11.0.min.js"></script>
    <script src="assets/js/jquery-migrate-1.2.1.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/templatemo.js"></script>
    <script src="assets/js/custom.js"></script>
    <!-- End Script -->
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

            // Hitung total pesanan
            const totalOrders = orderCounts.reduce((sum, count) => sum + count, 0);
            document.getElementById('total-orders').textContent = totalOrders;

            // Hitung total revenue
            const totalRevenue = revenueData.reduce((sum, revenue) => sum + revenue, 0);
            document.getElementById('total-revenue').textContent = 'Rp ' + formatNumber(totalRevenue);

            // Hitung rata-rata pesanan
            const avgOrder = totalOrders > 0 ? Math.round(totalRevenue / totalOrders) : 0;
            document.getElementById('avg-order').textContent = 'Rp ' + formatNumber(avgOrder);

            // Buat gradien untuk chart
            const ctx = document.getElementById('ordersChart').getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 0, 150); // Match to canvas height
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
                    maintainAspectRatio: true, // Changed to true to prevent height issues
                    aspectRatio: 2, // Width:height ratio
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
    </script>
</body>

</html>