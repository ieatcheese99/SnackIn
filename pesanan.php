<?php
// Pastikan session dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Koneksi ke database
$db = new mysqli("localhost", "root", "", "data_produk2");
if ($db->connect_error) {
    die("Koneksi gagal: " . $db->connect_error);
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get total orders count
$count_query = "SELECT COUNT(*) as total FROM orders";
$count_result = $db->query($count_query);
$total_orders = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_orders / $per_page);

// Get orders with pagination
$query = "SELECT * FROM orders ORDER BY created_at DESC LIMIT $offset, $per_page";
$result = $db->query($query);

// Handle order deletion
if (isset($_POST['delete_order']) && isset($_POST['order_id'])) {
    $order_id = (int)$_POST['order_id'];
    $delete_query = "DELETE FROM orders WHERE id = $order_id";
    if ($db->query($delete_query)) {
        // Redirect to refresh the page
        header("Location: pesanan.php");
        exit;
    }
}

// Handle viewing order details
$view_order = null;
$order_items = null;
if (isset($_GET['view']) && $_GET['view'] > 0) {
    $view_id = (int)$_GET['view'];
    $view_query = "SELECT * FROM orders WHERE id = $view_id";
    $view_result = $db->query($view_query);

    if ($view_result->num_rows > 0) {
        $view_order = $view_result->fetch_assoc();

        // Get order items
        $items_query = "SELECT * FROM order_items WHERE order_id = $view_id";
        $order_items = $db->query($items_query);
    }
}

// Function to get payment method badge
function getPaymentBadge($method)
{
    switch ($method) {
        case 'transfer_bank':
            return '<span class="badge bg-blue">Transfer Bank</span>';
        case 'cod':
            return '<span class="badge bg-amber">COD</span>';
        case 'e-wallet':
            return '<span class="badge bg-teal">E-Wallet</span>';
        default:
            return '<span class="badge bg-gray">' . htmlspecialchars($method) . '</span>';
    }
}

// Function to format date
function formatDate($date)
{
    return date('d M Y, H:i', strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $view_order ? "Detail Pesanan #" . $view_order['id'] : "Daftar Pesanan"; ?></title>

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
            --primary: #4361ee;
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

        /* Modal Styles */
        .modal-modern .modal-content {
            border-radius: 12px;
            border: none;
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        .modal-modern .modal-header {
            background-color: white;
            border-bottom: 1px solid var(--border);
            padding: 16px 24px;
        }

        .modal-modern .modal-body {
            padding: 24px;
        }

        .modal-modern .modal-footer {
            background-color: var(--gray-light);
            border-top: 1px solid var(--border);
            padding: 16px 24px;
        }

        /* Print Styles */
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background-color: white;
            }

            .card-modern {
                box-shadow: none;
                margin: 0;
                border: none;
            }

            .card-header-modern {
                color: black;
                background: white;
                border-bottom: 2px solid #eee;
            }

            .info-box {
                box-shadow: none;
                border: 1px solid #eee;
            }
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
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <h2 class="mb-0 fw-bold"><?php echo $view_order ? "Detail Pesanan #" . $view_order['id'] : "Daftar Pesanan"; ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active"><?php echo $view_order ? "Detail Pesanan" : "Pesanan"; ?></li>
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
                    <a href="pesanan.php" class="btn-modern btn-modern-secondary btn-modern-sm no-print">
                        <i class="fas fa-arrow-left"></i>
                        Kembali
                    </a>
                </div>
                <div class="card-body-modern">
                    <div class="row">
                        <div class="col-md-6">
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
                                            <th>Total</th>
                                            <td><span style="color: var(--primary); font-weight: 700;">Rp <?php echo number_format($view_order['total_harga'], 0, ',', '.'); ?></span></td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                <form method="POST" action="">
                                                    <input type="hidden" name="order_id" value="<?php echo $view_order['id']; ?>">
                                                    <div class="input-group">
                                                        <select name="status" id="status" class="form-select">
                                                            <option value="processing" <?php echo ($view_order['status'] ?? 'processing') == 'processing' ? 'selected' : ''; ?>>Sedang Diproses</option>
                                                            <option value="delivered" <?php echo ($view_order['status'] ?? '') == 'delivered' ? 'selected' : ''; ?>>Selesai</option>
                                                        </select>
                                                        <button type="submit" name="update_status" class="btn-modern btn-modern-primary btn-modern-sm">
                                                            <i class="fas fa-save"></i>
                                                            Simpan
                                                        </button>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
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
                                            <th>Alamat</th>
                                            <td><?php echo nl2br(htmlspecialchars($view_order['alamat'])); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
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
                                <?php if ($order_items && $order_items->num_rows > 0): ?>
                                    <?php while ($item = $order_items->fetch_assoc()): ?>
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

                    <div class="d-flex justify-content-between mt-4 no-print">
                        <a href="pesanan.php" class="btn-modern btn-modern-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Kembali
                        </a>
                        <div>
                            <button type="button" class="btn-modern btn-modern-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $view_order['id']; ?>">
                                <i class="fas fa-trash"></i>
                                Hapus Pesanan
                            </button>
                            <button type="button" class="btn-modern btn-modern-primary ms-2" onclick="window.print()">
                                <i class="fas fa-print"></i>
                                Cetak Pesanan
                            </button>
                        </div>
                    </div>

                    <!-- Delete Modal -->
                    <div class="modal fade modal-modern" id="deleteModal<?php echo $view_order['id']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Konfirmasi Hapus</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <div class="mb-4">
                                        <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: var(--warning);"></i>
                                    </div>
                                    <h5 class="mb-2">Apakah Anda yakin?</h5>
                                    <p class="mb-0">Pesanan <strong>#<?php echo $view_order['id']; ?></strong> akan dihapus secara permanen.</p>
                                    <p class="text-muted small mt-2">Tindakan ini tidak dapat dibatalkan</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn-modern btn-modern-secondary" data-bs-dismiss="modal">Batal</button>
                                    <form method="POST" action="pesanan.php">
                                        <input type="hidden" name="order_id" value="<?php echo $view_order['id']; ?>">
                                        <button type="submit" name="delete_order" class="btn-modern btn-modern-danger">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Daftar Pesanan View -->
            <div class="card-modern">
                <div class="card-header-modern">
                    <div>
                        <i class="fas fa-shopping-cart"></i>
                        Daftar Pesanan
                    </div>
                    <div>
                        <span class="badge bg-blue"><?php echo $total_orders; ?> pesanan</span>
                    </div>
                </div>
                <div class="card-body-modern p-0">
                    <?php if ($result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table-modern">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama</th>
                                        <th>Alamat</th>
                                        <th>Metode Pembayaran</th>
                                        <th>Total</th>
                                        <th>Tanggal</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($order = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong>#<?php echo $order['id']; ?></strong></td>
                                            <td><?php echo htmlspecialchars($order['nama']); ?></td>
                                            <td><?php echo htmlspecialchars(substr($order['alamat'], 0, 30)) . (strlen($order['alamat']) > 30 ? '...' : ''); ?></td>
                                            <td><?php echo getPaymentBadge($order['metode_pembayaran']); ?></td>
                                            <td><span style="color: var(--primary); font-weight: 600;">Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></span></td>
                                            <td><?php echo formatDate($order['created_at']); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="pesanan.php?view=<?php echo $order['id']; ?>" class="btn-modern btn-modern-primary btn-modern-sm">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn-modern btn-modern-danger btn-modern-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $order['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>

                                                <!-- Delete Modal -->
                                                <div class="modal fade modal-modern" id="deleteModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title fw-bold">Konfirmasi Hapus</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body text-center">
                                                                <div class="mb-4">
                                                                    <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: var(--warning);"></i>
                                                                </div>
                                                                <h5 class="mb-2">Apakah Anda yakin?</h5>
                                                                <p class="mb-0">Pesanan <strong>#<?php echo $order['id']; ?></strong> akan dihapus secara permanen.</p>
                                                                <p class="text-muted small mt-2">Tindakan ini tidak dapat dibatalkan</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn-modern btn-modern-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <form method="POST" action="">
                                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                                    <button type="submit" name="delete_order" class="btn-modern btn-modern-danger">Hapus</button>
                                                                </form>
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
                            <div class="p-3">
                                <ul class="pagination-modern">
                                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>

                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-shopping-cart"></i>
                            <h4>Belum Ada Pesanan</h4>
                            <p>Belum ada pesanan yang masuk ke sistem.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Optional: DataTables for enhanced table functionality -->
    <script>
        $(document).ready(function() {
            // Add any JavaScript functionality here

            // Example: Add confirmation for delete buttons
            $('.btn-modern-danger').click(function() {
                return confirm('Apakah Anda yakin ingin menghapus pesanan ini?');
            });
        });
    </script>
</body>

</html>
