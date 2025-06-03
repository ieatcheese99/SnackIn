<?php
// Define admin access constant
define('ADMIN_ACCESS', true);

// Cek akses admin
require_once 'config/database.php';

session_start();

// Fungsi untuk memeriksa akses admin
function requireAdmin() {
    if (!isset($_SESSION['username']) || $_SESSION['level'] !== 'admin') {
        header("Location: login.php");
        exit();
    }
}

requireAdmin();

// Build the main query with proper column names - NO FILTERS
$query = "SELECT b.id, b.nama, b.Deskripsi, b.Stok as stok, b.Harga as harga, b.gambar, b.kategori_id, k.nama as kategori_nama 
          FROM barang b 
          LEFT JOIN kategori k ON b.kategori_id = k.id
          ORDER BY b.id DESC";

$products = mysqli_query($db, $query);

$page_title = "Data Barang";
include 'include/admin_header.php';
?>

<style>
    :root {
        --primary-color: #00227c;
        --secondary-color: #001a5e;
        --accent-color: #f48c06;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
        --dark-blue: #00227c;
        --white: #ffffff;
        --orange-bg: #f69e22;
        --transition: all 0.3s ease;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: var(--orange-bg);
        min-height: 100vh;
        color: #333;
    }

    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        padding: 40px 0;
        margin-bottom: 30px;
        text-align: center;
    }

    .page-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .page-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
    }

    /* Navigation Buttons */
    .nav-buttons {
        display: flex;
        gap: 10px;
        margin-bottom: 30px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .nav-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        background: rgba(255, 255, 255, 0.9);
        color: var(--primary-color);
        text-decoration: none;
        border-radius: 25px;
        font-weight: 600;
        transition: var(--transition);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .nav-btn:hover {
        background: white;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        color: var(--primary-color);
    }

    .nav-btn.active {
        background: var(--primary-color);
        color: white;
    }

    /* Content Container */
    .content-container {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        padding: 30px;
        margin: 20px;
        backdrop-filter: blur(10px);
    }

    /* Animation for Content */
    .content-animate {
        animation: fadeInUp 0.5s ease-out;
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

    /* Animation for Items */
    .animate-item {
        animation: fadeIn 0.7s ease-out backwards;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    /* Statistics Cards */
    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border-left: 5px solid var(--primary-color);
        transition: var(--transition);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .stat-number {
        font-size: 32px;
        font-weight: 800;
        color: var(--primary-color);
        display: block;
    }

    .stat-label {
        color: #666;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 5px;
    }

    /* Table Container */
    .table-container {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        margin-top: 20px;
    }

    .enhanced-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
        background: white;
    }

    .enhanced-table thead th {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        padding: 18px 15px;
        text-align: left;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 14px;
        border: none;
    }

    .enhanced-table tbody td {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }

    .enhanced-table tbody tr {
        transition: var(--transition);
    }

    .enhanced-table tbody tr:hover {
        background-color: #f8f9ff;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    /* Product Image in Table */
    .product-image-small {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #f0f0f0;
    }

    .product-placeholder {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #f0f0f0 0%, #e0e0e0 100%);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #999;
        font-size: 20px;
    }

    /* Product Info */
    .product-info h6 {
        margin: 0 0 5px 0;
        font-weight: 700;
        color: #333;
        font-size: 16px;
    }

    .product-info .category {
        display: inline-block;
        background: var(--primary-color);
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .product-info .description {
        color: #666;
        font-size: 13px;
        margin-top: 5px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Price Display */
    .price-display {
        font-size: 18px;
        font-weight: 700;
        color: var(--primary-color);
    }

    /* Stock Badge */
    .stock-info {
        text-align: center;
    }

    .stock-number {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        display: block;
        margin-bottom: 5px;
    }

    .stock-badge {
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .in-stock {
        background: #d1fae5;
        color: #047857;
    }

    .low-stock {
        background: #fef3c7;
        color: #b45309;
    }

    .out-of-stock {
        background: #fee2e2;
        color: #b91c1c;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: center;
    }

    .btn-action {
        padding: 8px 12px;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .btn-edit {
        background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%);
        color: white;
    }

    .btn-edit:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        color: white;
    }

    .btn-delete {
        background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%);
        color: white;
        border: none;
    }

    .btn-delete:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        color: white;
    }

    /* Add Button */
    .btn-add {
        background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
        color: white;
        padding: 12px 25px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 600;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        margin-bottom: 20px;
    }

    .btn-add:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        color: white;
    }

    /* Empty State */
    .no-products {
        text-align: center;
        padding: 60px 20px;
        color: #6b7280;
    }

    .no-products i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
        color: #ccc;
    }

    .no-products h5 {
        font-size: 1.5rem;
        margin-bottom: 10px;
        color: #374151;
    }

    .no-products p {
        font-size: 1rem;
        max-width: 400px;
        margin: 0 auto;
    }

    /* Delete Modal */
    .delete-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 10000;
        backdrop-filter: blur(5px);
    }

    .delete-modal.show {
        display: flex;
    }

    .delete-modal-content {
        background: white;
        padding: 30px;
        border-radius: 15px;
        text-align: center;
        max-width: 400px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        margin: 20px;
        animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .delete-icon {
        width: 60px;
        height: 60px;
        background: #fee2e2;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }

    .delete-icon i {
        color: #ef4444;
        font-size: 24px;
    }

    .delete-modal h3 {
        margin-bottom: 15px;
        color: #333;
    }

    .delete-modal p {
        margin-bottom: 10px;
        color: #666;
    }

    .product-name-highlight {
        margin-bottom: 25px;
        color: #333;
        font-weight: 600;
    }

    .warning-text {
        margin-bottom: 25px;
        color: #ef4444;
        font-size: 14px;
    }

    .modal-buttons {
        display: flex;
        gap: 10px;
        justify-content: center;
    }

    .modal-btn {
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        transition: var(--transition);
        border: none;
        text-decoration: none;
        display: inline-block;
    }

    .btn-cancel {
        border: 2px solid #ddd;
        background: white;
        color: #666;
    }

    .btn-cancel:hover {
        background: #f5f5f5;
    }

    .btn-confirm {
        background: var(--primary-color);
        color: white;
    }

    .btn-confirm:hover {
        background: var(--secondary-color);
    }

    /* Responsive Design */
    @media (max-width: 992px) {
        .enhanced-table {
            font-size: 14px;
        }
        
        .enhanced-table thead th,
        .enhanced-table tbody td {
            padding: 12px 8px;
        }
    }

    @media (max-width: 768px) {
        .content-container {
            margin: 10px;
            padding: 20px;
        }

        .nav-buttons {
            justify-content: center;
        }

        .nav-btn {
            padding: 10px 15px;
            font-size: 0.9rem;
        }

        .stats-cards {
            grid-template-columns: repeat(2, 1fr);
        }

        .action-buttons {
            flex-direction: column;
            gap: 5px;
        }

        .btn-action {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 576px) {
        .enhanced-table {
            font-size: 12px;
        }

        .product-image-small,
        .product-placeholder {
            width: 40px;
            height: 40px;
        }

        .stats-cards {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1 class="page-title"><i class="fas fa-box"></i> Data Barang</h1>
        <p class="page-subtitle">Kelola produk dan inventori toko Anda</p>
    </div>
</div>

<div class="container-fluid">
    <!-- Navigation Buttons -->
    <div class="nav-buttons">
        <a href="index.php" class="nav-btn" onclick="showLoading()">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="data_barang.php" class="nav-btn active">
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

    <div class="content-container content-animate">
        <!-- Statistics Cards -->
        <div class="stats-cards">
            <?php
            // Get statistics with proper error handling
            $total_products = $products ? mysqli_num_rows($products) : 0;
            
            $low_stock_query = "SELECT COUNT(*) as count FROM barang WHERE Stok <= 5";
            $low_stock_result = mysqli_query($db, $low_stock_query);
            $low_stock_count = $low_stock_result ? mysqli_fetch_assoc($low_stock_result)['count'] : 0;
            
            $out_of_stock_query = "SELECT COUNT(*) as count FROM barang WHERE Stok = 0";
            $out_of_stock_result = mysqli_query($db, $out_of_stock_query);
            $out_of_stock_count = $out_of_stock_result ? mysqli_fetch_assoc($out_of_stock_result)['count'] : 0;
            ?>
            <div class="stat-card animate-item">
                <span class="stat-number"><?php echo number_format($total_products); ?></span>
                <div class="stat-label">Total Produk</div>
            </div>
            <div class="stat-card animate-item">
                <span class="stat-number"><?php echo number_format($low_stock_count); ?></span>
                <div class="stat-label">Stok Menipis</div>
            </div>
            <div class="stat-card animate-item">
                <span class="stat-number"><?php echo number_format($out_of_stock_count); ?></span>
                <div class="stat-label">Stok Habis</div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="text-center">
            <a href="form_tambah.php" class="btn-add" onclick="showLoading()">
                <i class="fas fa-plus-circle"></i> Tambah Produk Baru
            </a>
        </div>

        <!-- Products Table -->
        <?php if ($products && mysqli_num_rows($products) > 0): ?>
            <div class="table-container animate-item">
                <table class="enhanced-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> ID</th>
                            <th><i class="fas fa-image"></i> Gambar</th>
                            <th><i class="fas fa-box"></i> Produk</th>
                            <th><i class="fas fa-money-bill"></i> Harga</th>
                            <th><i class="fas fa-warehouse"></i> Stok</th>
                            <th><i class="fas fa-calendar"></i> Status</th>
                            <th><i class="fas fa-cogs"></i> Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        mysqli_data_seek($products, 0);
                        while ($product = mysqli_fetch_assoc($products)): 
                            // Ensure all required fields exist with default values
                            $product_id = $product['id'] ?? 0;
                            $product_name = $product['nama'] ?? 'Nama tidak tersedia';
                            $product_price = isset($product['harga']) ? (float)$product['harga'] : 0;
                            $product_stock = isset($product['stok']) ? (int)$product['stok'] : 0;
                            $product_image = $product['gambar'] ?? '';
                            $product_category = $product['kategori_nama'] ?? 'Tanpa Kategori';
                            $product_description = $product['Deskripsi'] ?? '';
                            
                            // Fix image path - handle both cases
                            $image_path = '';
                            if (!empty($product_image)) {
                                // If path already contains 'uploads/', use as is
                                if (strpos($product_image, 'uploads/') === 0) {
                                    $image_path = $product_image;
                                } else {
                                    // Add 'uploads/' prefix
                                    $image_path = 'uploads/' . $product_image;
                                }
                            }
                            
                            // Determine stock status
                            $stock_status = '';
                            $stock_class = '';
                            if ($product_stock == 0) {
                                $stock_status = 'Habis';
                                $stock_class = 'out-of-stock';
                            } elseif ($product_stock <= 5) {
                                $stock_status = 'Menipis';
                                $stock_class = 'low-stock';
                            } else {
                                $stock_status = 'Tersedia';
                                $stock_class = 'in-stock';
                            }
                        ?>
                            <tr>
                                <td><strong>#<?php echo $product_id; ?></strong></td>
                                <td>
                                    <?php if (!empty($image_path) && file_exists($image_path)): ?>
                                        <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                             alt="<?php echo htmlspecialchars($product_name); ?>" 
                                             class="product-image-small"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="product-placeholder" style="display: none;">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="product-placeholder">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="product-info">
                                        <h6><?php echo htmlspecialchars($product_name); ?></h6>
                                        <span class="category"><?php echo htmlspecialchars($product_category); ?></span>
                                        <?php if (!empty($product_description)): ?>
                                            <div class="description"><?php echo htmlspecialchars($product_description); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="price-display">
                                        Rp <?php echo number_format($product_price, 0, ',', '.'); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="stock-info">
                                        <span class="stock-number"><?php echo number_format($product_stock); ?></span>
                                        <span class="stock-badge <?php echo $stock_class; ?>">
                                            <?php echo $stock_status; ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="stock-badge <?php echo $stock_class; ?>">
                                        <i class="fas fa-<?php echo $product_stock > 5 ? 'check-circle' : ($product_stock > 0 ? 'exclamation-triangle' : 'times-circle'); ?>"></i>
                                        <?php echo $stock_status; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="form_ubah.php?id=<?php echo $product_id; ?>" class="btn-action btn-edit" onclick="showLoading()">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <button class="btn-action btn-delete" 
                                                data-product-id="<?php echo $product_id; ?>"
                                                data-product-name="<?php echo htmlspecialchars($product_name); ?>"
                                                onclick="showDeleteModal(this)">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-products">
                <i class="fas fa-box-open"></i>
                <h5>Belum ada produk</h5>
                <p>Produk akan muncul di sini setelah ditambahkan</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="delete-modal" id="deleteModal">
    <div class="delete-modal-content">
        <div class="delete-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3>Konfirmasi Hapus Produk</h3>
        <p>Apakah Anda yakin ingin menghapus produk:</p>
        <div class="product-name-highlight" id="productNameDisplay"></div>
        <div class="warning-text">Tindakan ini tidak dapat dibatalkan!</div>
        <div class="modal-buttons">
            <button class="modal-btn btn-cancel" onclick="hideDeleteModal()">Batal</button>
            <a href="#" class="modal-btn btn-confirm" id="confirmDeleteBtn">Ya, Hapus Produk</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Show delete modal
    function showDeleteModal(button) {
        const productId = button.getAttribute('data-product-id');
        const productName = button.getAttribute('data-product-name');
        
        document.getElementById('productNameDisplay').textContent = `"${productName}"`;
        document.getElementById('confirmDeleteBtn').href = `form_delete.php?id=${productId}`;
        document.getElementById('deleteModal').classList.add('show');
    }

    // Hide delete modal
    function hideDeleteModal() {
        document.getElementById('deleteModal').classList.remove('show');
    }

    // Close modal when clicking outside
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideDeleteModal();
        }
    });

    // Prevent modal from closing when clicking inside content
    document.querySelector('.delete-modal-content').addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Add loading to confirm delete button
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        showLoading();
    });

    // Add loading animation for edit buttons
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function() {
            showLoading();
        });
    });

    // Add loading animation for add button
    document.querySelectorAll('.btn-add').forEach(button => {
        button.addEventListener('click', function() {
            showLoading();
        });
    });

    // Add loading animation for actions
    function showLoading() {
        document.querySelectorAll('.btn-edit, .btn-add, .btn-delete').forEach(button => {
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        });
    }
</script>

<?php include 'include/admin_footer.php'; ?>
