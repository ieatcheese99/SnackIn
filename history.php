<?php
session_start();
include 'include/koneksi.php';

// Check if user is logged in
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// Get order history
$query = mysqli_query($koneksi, "SELECT o.*, COUNT(oi.id) as item_count 
                                FROM orders o 
                                LEFT JOIN order_items oi ON o.id = oi.order_id 
                                WHERE o.id_user = '$id_user' 
                                GROUP BY o.id 
                                ORDER BY o.order_date DESC");
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
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .order-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .status-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            visibility: hidden;
            opacity: 0;
            transition: visibility 0s, opacity 0.3s linear;
        }
        
        .loading-overlay.show {
            visibility: visible;
            opacity: 1;
        }
        
        .spinner-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .spinner-text {
            margin-top: 10px;
            font-weight: 500;
            color: #333;
        }
        
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
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
    </style>
</head>
<body>
    <?php include 'include/user_header.php'; ?>
    
    <!-- Main Content -->
    <div class="container py-5">
        <h2 class="mb-4">Order History</h2>
        
        <?php if (mysqli_num_rows($query) > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php while ($order = mysqli_fetch_assoc($query)): ?>
                    <div class="col">
                        <div class="card order-card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Order #<?php echo $order['id']; ?></span>
                                <?php
                                $status_class = '';
                                switch ($order['status']) {
                                    case 'pending':
                                        $status_class = 'bg-warning';
                                        break;
                                    case 'processing':
                                        $status_class = 'bg-info';
                                        break;
                                    case 'completed':
                                        $status_class = 'bg-success';
                                        break;
                                    case 'cancelled':
                                        $status_class = 'bg-danger';
                                        break;
                                    default:
                                        $status_class = 'bg-secondary';
                                }
                                ?>
                                <span class="badge <?php echo $status_class; ?> status-badge"><?php echo ucfirst($order['status']); ?></span>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <small class="text-muted">Order Date:</small>
                                    <p class="mb-0"><?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></p>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted">Total Amount:</small>
                                    <p class="mb-0 fw-bold">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></p>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted">Items:</small>
                                    <p class="mb-0"><?php echo $order['item_count']; ?> items</p>
                                </div>
                                <button class="btn btn-sm btn-outline-primary w-100 btn-view-details" data-id="<?php echo $order['id']; ?>" data-bs-toggle="modal" data-bs-target="#orderDetailsModal">
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-history">
                <i class="fas fa-history"></i>
                <h4>No order history</h4>
                <p>You haven't placed any orders yet.</p>
                <a href="user_ui.php" class="btn btn-primary mt-3">Start Shopping</a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderDetailsModalLabel">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center" id="orderDetailsLoading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading order details...</p>
                    </div>
                    <div id="orderDetailsContent" style="display: none;">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>Order Information</h6>
                                <p><strong>Order ID:</strong> <span id="orderIdDetail"></span></p>
                                <p><strong>Date:</strong> <span id="orderDateDetail"></span></p>
                                <p><strong>Status:</strong> <span id="orderStatusDetail"></span></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Payment Information</h6>
                                <p><strong>Total Amount:</strong> <span id="orderTotalDetail"></span></p>
                            </div>
                        </div>
                        <h6>Order Items</h6>
                        <div class="order-items">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-container">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
            <div class="spinner-text">Processing...</div>
        </div>
    </div>
    
    <!-- Toast Container -->
    <div class="toast-container"></div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Show loading overlay
            function showLoading() {
                $('#loadingOverlay').addClass('show');
            }
            
            // Hide loading overlay
            function hideLoading() {
                $('#loadingOverlay').removeClass('show');
            }
            
            // Show toast notification
            function showToast(message, type = 'success') {
                const toastId = 'toast-' + Date.now();
                const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
                
                const toast = `
                    <div class="toast ${bgClass} text-white" id="${toastId}" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header">
                            <strong class="me-auto">${type === 'success' ? 'Success' : 'Error'}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            ${message}
                        </div>
                    </div>
                `;
                
                $('.toast-container').append(toast);
                
                const toastElement = new bootstrap.Toast(document.getElementById(toastId), {
                    delay: 3000
                });
                
                toastElement.show();
                
                // Remove toast from DOM after it's hidden
                $(`#${toastId}`).on('hidden.bs.toast', function() {
                    $(this).remove();
                });
            }
            
            // View order details
            $('.btn-view-details').click(function() {
                const orderId = $(this).data('id');
                
                // Reset modal content
                $('#orderDetailsContent').hide();
                $('#orderDetailsLoading').show();
                
                // Load order details via AJAX
                $.ajax({
                    url: 'include/get_order_details.php',
                    type: 'GET',
                    data: {
                        order_id: orderId
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#orderDetailsLoading').hide();
                        
                        if (response.status === 'success') {
                            // Fill order details
                            $('#orderIdDetail').text(response.order.id);
                            $('#orderDateDetail').text(response.order.order_date);
                            
                            // Set status with badge
                            let statusClass = '';
                            switch (response.order.status) {
                                case 'pending':
                                    statusClass = 'bg-warning';
                                    break;
                                case 'processing':
                                    statusClass = 'bg-info';
                                    break;
                                case 'completed':
                                    statusClass = 'bg-success';
                                    break;
                                case 'cancelled':
                                    statusClass = 'bg-danger';
                                    break;
                                default:
                                    statusClass = 'bg-secondary';
                            }
                            
                            $('#orderStatusDetail').html(`<span class="badge ${statusClass}">${response.order.status}</span>`);
                            $('#orderTotalDetail').text('Rp ' + parseFloat(response.order.total_amount).toLocaleString('id-ID'));
                            
                            // Fill order items
                            let itemsHtml = '';
                            
                            if (response.items.length > 0) {
                                response.items.forEach(function(item) {
                                    const subtotal = item.price * item.quantity;
                                    
                                    itemsHtml += `
                                        <tr>
                                            <td>${item.nama_produk}</td>
                                            <td>Rp ${parseFloat(item.price).toLocaleString('id-ID')}</td>
                                            <td>${item.quantity}</td>
                                            <td>Rp ${subtotal.toLocaleString('id-ID')}</td>
                                        </tr>
                                    `;
                                });
                            } else {
                                itemsHtml = `
                                    <tr>
                                        <td colspan="4" class="text-center">No items found</td>
                                    </tr>
                                `;
                            }
                            
                            $('#orderItemsDetail').html(itemsHtml);
                            $('#orderDetailsContent').show();
                        } else {
                            showToast(response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#orderDetailsLoading').hide();
                        showToast('An error occurred: ' + error, 'error');
                    }
                });
            });
        });
    </script>
</body>
</html>
