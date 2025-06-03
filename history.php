<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['id']) && !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Get user ID from session
$id_user = isset($_SESSION['id']) ? $_SESSION['id'] : null;

// If id_user is not set, get it from username
if (!$id_user && isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $userQuery = "SELECT id FROM user WHERE username = ?";
    $stmt = mysqli_prepare($db, $userQuery);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $userData = mysqli_fetch_assoc($result);
    $id_user = $userData['id'];
    $_SESSION['id'] = $id_user;
}

// Get order history
$query = "SELECT o.*, COUNT(oi.id) as item_count 
          FROM orders o 
          LEFT JOIN order_items oi ON o.id = oi.order_id 
          WHERE o.username = ? 
          GROUP BY o.id 
          ORDER BY o.created_at DESC";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "s", $_SESSION['username']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SnackIn - Order History</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }

        /* Main Header */
        .main-header {
            background-color: #00227c;
            color: white;
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .main-header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 700;
            text-decoration: none;
            color: white;
        }

        .logo:hover {
            color: white;
        }

        .logo img {
            height: 40px;
            width: auto;
        }

        .main-nav ul {
            display: flex;
            gap: 30px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .main-nav a {
            font-weight: 500;
            transition: color 0.3s ease;
            position: relative;
            color: white;
            text-decoration: none;
        }

        .main-nav a:hover {
            color: rgba(255, 255, 255, 0.8);
        }

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .action-icon {
            position: relative;
            font-size: 18px;
            cursor: pointer;
            transition: color 0.3s ease;
            color: white;
            text-decoration: none;
        }

        .action-icon:hover {
            color: rgba(255, 255, 255, 0.8);
        }

        .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: white;
            color: #00227c;
            font-size: 10px;
            font-weight: 600;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .order-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .status-badge {
            font-size: 0.9rem;
            padding: 0.4rem 0.8rem;
            font-weight: 600;
            border-radius: 20px;
            min-width: 80px;
            text-align: center;
            display: inline-block;
        }
        
        .empty-history {
            text-align: center;
            padding: 50px 0;
        }
        
        .empty-history i {
            font-size: 5rem;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        
        .order-items {
            max-height: 200px;
            overflow-y: auto;
        }

        @media (max-width: 768px) {
            .main-nav {
                display: none;
            }
        }
    </style>
</head>
<body>
    <?php 
// Set cart count for the header
$cartCount = 0;
if (isset($_SESSION['cart'])) {
    $cartCount = count($_SESSION['cart']);
}
// Include the user header
include_once 'user_header.php'; 
?>
    
    <!-- Main Content -->
    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4" style="color: #00227c; font-weight: 700;">
                    <i class="fas fa-history me-2"></i>Riwayat Pesanan
                </h2>
            </div>
        </div>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php while ($order = mysqli_fetch_assoc($result)): ?>
                    <div class="col">
                        <div class="card order-card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center" style="background: #f8f9fa; border-bottom: 2px solid #e9ecef;">
                                <span style="font-weight: 600; color: #00227c;">Order #<?php echo $order['id']; ?></span>
                                <?php
                                $status_class = '';
                                switch ($order['status']) {
                                    case 'pending':
                                        $status_class = 'bg-warning text-dark';
                                        break;
                                    case 'processing':
                                        $status_class = 'bg-info text-white';
                                        break;
                                    case 'completed':
                                        $status_class = 'bg-success text-white';
                                        break;
                                    case 'cancelled':
                                        $status_class = 'bg-danger text-white';
                                        break;
                                    default:
                                        $status_class = 'bg-secondary text-white';
                                }
                                ?>
                                <span class="badge <?php echo $status_class; ?> status-badge"><?php echo ucfirst($order['status']); ?></span>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <small class="text-muted">Tanggal Pesanan:</small>
                                    <p class="mb-0 fw-semibold"><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></p>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted">Total Pembayaran:</small>
                                    <p class="mb-0 fw-bold" style="color: #00227c; font-size: 1.1rem;">Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></p>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted">Jumlah Item:</small>
                                    <p class="mb-0"><?php echo $order['item_count']; ?> items</p>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted">Metode Pembayaran:</small>
                                    <p class="mb-0"><?php echo htmlspecialchars($order['metode_pembayaran']); ?></p>
                                </div>
                                <button class="btn btn-outline-primary w-100 btn-view-details" 
                                        data-id="<?php echo $order['id']; ?>" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#orderDetailsModal"
                                        style="border-color: #00227c; color: #00227c;">
                                    <i class="fas fa-eye me-2"></i>Lihat Detail
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-history">
                <i class="fas fa-history"></i>
                <h4>Belum ada riwayat pesanan</h4>
                <p>Anda belum pernah melakukan pemesanan.</p>
                <a href="user_ui.php" class="btn btn-primary mt-3" style="background: #00227c; border-color: #00227c;">
                    <i class="fas fa-shopping-bag me-2"></i>Mulai Belanja
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: #00227c; color: white;">
                    <h5 class="modal-title" id="orderDetailsModalLabel">Detail Pesanan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center" id="orderDetailsLoading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Memuat detail pesanan...</p>
                    </div>
                    <div id="orderDetailsContent" style="display: none;">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 style="color: #00227c; font-weight: 600;">Informasi Pesanan</h6>
                                <p><strong>Order ID:</strong> <span id="orderIdDetail"></span></p>
                                <p><strong>Tanggal:</strong> <span id="orderDateDetail"></span></p>
                                <p><strong>Status:</strong> <span id="orderStatusDetail"></span></p>
                                <p><strong>Metode Pembayaran:</strong> <span id="orderPaymentDetail"></span></p>
                            </div>
                            <div class="col-md-6">
                                <h6 style="color: #00227c; font-weight: 600;">Informasi Pengiriman</h6>
                                <p><strong>Nama:</strong> <span id="orderNameDetail"></span></p>
                                <p><strong>Alamat:</strong> <span id="orderAddressDetail"></span></p>
                                <p><strong>Total:</strong> <span id="orderTotalDetail" style="color: #00227c; font-weight: 700;"></span></p>
                            </div>
                        </div>
                        <h6 style="color: #00227c; font-weight: 600;">Item Pesanan</h6>
                        <div class="order-items">
                            <table class="table table-striped">
                                <thead style="background: #f8f9fa;">
                                    <tr>
                                        <th>Produk</th>
                                        <th>Harga</th>
                                        <th>Jumlah</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="orderItemsDetail">
                                    <!-- Order items will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Update cart count
            $.ajax({
                url: "include/cart_action.php",
                type: "POST",
                data: {
                    action: "count"
                },
                success: function(count) {
                    $(".cart-count").text(count);
                }
            });

            // View order details
            $('.btn-view-details').click(function() {
                const orderId = $(this).data('id');
                
                // Reset modal content
                $('#orderDetailsContent').hide();
                $('#orderDetailsLoading').show();
                
                // Load order details via AJAX
                $.ajax({
                    url: 'config/get_order_details.php',
                    type: 'GET',
                    data: {
                        order_id: orderId
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#orderDetailsLoading').hide();
                        
                        if (response.status === 'success') {
                            // Fill order details
                            $('#orderIdDetail').text('#' + response.order.id);
                            $('#orderDateDetail').text(response.order.order_date);
                            $('#orderNameDetail').text(response.order.nama);
                            $('#orderAddressDetail').text(response.order.alamat);
                            $('#orderPaymentDetail').text(response.order.metode_pembayaran);
                            
                            // Set status with badge
                            let statusClass = '';
                            switch (response.order.status) {
                                case 'pending':
                                    statusClass = 'bg-warning text-dark';
                                    break;
                                case 'processing':
                                    statusClass = 'bg-info text-white';
                                    break;
                                case 'completed':
                                    statusClass = 'bg-success text-white';
                                    break;
                                case 'cancelled':
                                    statusClass = 'bg-danger text-white';
                                    break;
                                default:
                                    statusClass = 'bg-secondary text-white';
                            }
                            
                            $('#orderStatusDetail').html(`<span class="badge ${statusClass}">${response.order.status}</span>`);
                            $('#orderTotalDetail').text('Rp ' + parseFloat(response.order.total_harga).toLocaleString('id-ID'));
                            
                            // Fill order items
                            let itemsHtml = '';
                            
                            if (response.items.length > 0) {
                                response.items.forEach(function(item) {
                                    const subtotal = item.harga * item.jumlah;
                                    
                                    itemsHtml += `
                                        <tr>
                                            <td>${item.nama_produk}</td>
                                            <td>Rp ${parseFloat(item.harga).toLocaleString('id-ID')}</td>
                                            <td>${item.jumlah}</td>
                                            <td>Rp ${subtotal.toLocaleString('id-ID')}</td>
                                        </tr>
                                    `;
                                });
                            } else {
                                itemsHtml = `
                                    <tr>
                                        <td colspan="4" class="text-center">Tidak ada item ditemukan</td>
                                    </tr>
                                `;
                            }
                            
                            $('#orderItemsDetail').html(itemsHtml);
                            $('#orderDetailsContent').show();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#orderDetailsLoading').hide();
                        alert('Terjadi kesalahan: ' + error);
                    }
                });
            });
        });
    </script>
</body>
</html>
