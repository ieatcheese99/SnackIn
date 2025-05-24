<?php
session_start();
require "config/database.php";

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: history.php");
    exit();
}

$order_id = (int)$_GET['id'];

// Get order details
$query = "SELECT * FROM orders WHERE id = ? AND nama = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("is", $order_id, $username);
$stmt->execute();
$order_result = $stmt->get_result();

// If order not found or doesn't belong to user, redirect
if ($order_result->num_rows == 0) {
    header("Location: history.php");
    exit();
}

$order = $order_result->fetch_assoc();

// Get order items
$query_items = "SELECT * FROM order_items WHERE order_id = ?";
$stmt_items = $db->prepare($query_items);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items_result = $stmt_items->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?php echo $order_id; ?> - Snack In</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f9f9;
        }
        .order-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .order-header {
            background-color: #00227c;
            color: white;
            padding: 15px 20px;
            font-weight: 600;
        }
        .order-body {
            padding: 20px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-processing {
            background-color: #e0f2fe;
            color: #0369a1;
        }
        .status-shipped {
            background-color: #dcfce7;
            color: #166534;
        }
        .status-delivered {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-cancelled {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .item-card {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="bg-primary text-white py-3">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h4 mb-0">Detail Pesanan #<?php echo $order_id; ?></h1>
                <a href="history.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container py-5">
        <div class="order-card mb-4">
            <div class="order-header d-flex justify-content-between align-items-center">
                <div>Order #<?php echo $order['id']; ?></div>
                <div>
                    <span class="status-badge <?php echo 'status-' . ($order['status'] ?? 'pending'); ?>">
                        <?php 
                        $status = $order['status'] ?? 'processing';
                        echo ($status == 'delivered') ? 'Selesai' : 'Sedang Diproses';
                        ?>
                    </span>
                </div>
            </div>
            <div class="order-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Informasi Pesanan</h5>
                        <p><strong>Tanggal Pesanan:</strong> <?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></p>
                        <p><strong>Metode Pembayaran:</strong> <?php echo $order['metode_pembayaran']; ?></p>
                        <p><strong>Total:</strong> <span class="text-primary fw-bold">Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></span></p>
                    </div>
                    <div class="col-md-6">
                        <h5>Informasi Pengiriman</h5>
                        <p><strong>Nama:</strong> <?php echo htmlspecialchars($order['nama']); ?></p>
                        <p><strong>Alamat:</strong><br><?php echo nl2br(htmlspecialchars($order['alamat'])); ?></p>
                    </div>
                </div>

                <h5 class="mb-3">Item Pesanan</h5>
                <?php if ($items_result->num_rows > 0): ?>
                    <?php while ($item = $items_result->fetch_assoc()): ?>
                        <div class="item-card">
                            <div class="row align-items-center">
                                <div class="col-md-2 col-4">
                                    <img src="<?php echo $item['gambar_produk']; ?>" alt="<?php echo $item['nama_produk']; ?>" class="item-img">
                                </div>
                                <div class="col-md-6 col-8">
                                    <h6><?php echo htmlspecialchars($item['nama_produk']); ?></h6>
                                    <p class="text-muted mb-0">Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?> x <?php echo $item['jumlah']; ?></p>
                                </div>
                                <div class="col-md-4 col-12 mt-3 mt-md-0 text-md-end">
                                    <p class="fw-bold mb-0">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Tidak ada item dalam pesanan ini.
                    </div>
                <?php endif; ?>

                <div class="row mt-4">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <div class="bg-light p-3 rounded">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Pengiriman:</span>
                                <span>Gratis</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total:</span>
                                <span class="text-primary">Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (($order['status'] ?? 'pending') == 'pending'): ?>
                <div class="mt-4">
                    <button class="btn btn-danger cancel-order" data-id="<?php echo $order['id']; ?>">
                        Batalkan Pesanan
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Snack In</h5>
                    <p>Rasakan Nikmatnya Snack Premium Indonesia!</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; 2025 Snack In. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Cancel order functionality
            $('.cancel-order').click(function() {
                if (confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')) {
                    var orderId = $(this).data('id');
                    $.post('cancel_order.php', {
                        order_id: orderId
                    }, function(response) {
                        try {
                            var result = JSON.parse(response);
                            if (result.success) {
                                alert('Pesanan berhasil dibatalkan');
                                location.reload();
                            } else {
                                alert('Gagal membatalkan pesanan: ' + (result.error || 'Unknown error'));
                            }
                        } catch (e) {
                            console.error('Error parsing response:', response);
                            alert('Terjadi kesalahan saat membatalkan pesanan');
                        }
                    }).fail(function() {
                        alert('Terjadi kesalahan koneksi');
                    });
                }
            });
        });
    </script>
</body>
</html>
