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

// Handle filtering
$where_conditions = [];
$params = [];
$types = '';

if (isset($_GET['kategori']) && !empty($_GET['kategori'])) {
    $where_conditions[] = "b.kategori_id = ?";
    $params[] = $_GET['kategori'];
    $types .= 'i';
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where_conditions[] = "b.nama LIKE ?";
    $params[] = '%' . $_GET['search'] . '%';
    $types .= 's';
}

// Build the main query with proper column names
$query = "SELECT b.id, b.nama, b.Deskripsi, b.Stok as stok, b.Harga as harga, b.gambar, b.kategori_id, k.nama as kategori_nama 
          FROM barang b 
          LEFT JOIN kategori k ON b.kategori_id = k.id";

if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}

$query .= " ORDER BY b.id DESC";

// Execute query with prepared statement if there are parameters
if (!empty($params)) {
    $stmt = mysqli_prepare($db, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $products = mysqli_stmt_get_result($stmt);
    } else {
        $products = mysqli_query($db, $query);
    }
} else {
    $products = mysqli_query($db, $query);
}

// Get categories for filter
$categories_query = "SELECT * FROM kategori ORDER BY nama";
$categories = mysqli_query($db, $categories_query);

$page_title = "Data Barang";
include 'include/admin_header.php';
?>

<style>
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 25px;
        margin-top: 30px;
    }

    .product-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        position: relative;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
    }

    .product-image {
        height: 200px;
        width: 100%;
        object-fit: cover;
        border-bottom: 1px solid #f0f0f0;
    }

    .product-content {
        padding: 20px;
    }

    .product-category {
        display: inline-block;
        background: #00227c;
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .product-name {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 10px;
        color: #333;
        line-height: 1.3;
        min-height: 48px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-price {
        font-size: 20px;
        font-weight: 800;
        color: #00227c;
        margin-bottom: 15px;
    }

    .product-description {
        font-size: 14px;
        color: #666;
        margin-bottom: 15px;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-stock {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 15px;
        font-size: 14px;
        color: #666;
    }

    .stock-badge {
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
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

    .product-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .btn-edit, .btn-delete {
        padding: 8px 16px;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        flex: 1;
        justify-content: center;
        font-size: 13px;
    }

    .btn-edit {
        background: #f59e0b;
        color: white;
    }

    .btn-edit:hover {
        background: #d97706;
        transform: translateY(-2px);
        color: white;
    }

    .btn-delete {
        background: #ef4444;
        color: white;
    }

    .btn-delete:hover {
        background: #dc2626;
        transform: translateY(-2px);
        color: white;
    }

    .btn-add {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 12px 25px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        color: white;
    }

    .filters {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .filter-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #333;
    }

    .filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: end;
    }

    .filter-group {
        flex: 1;
        min-width: 200px;
    }

    .filter-label {
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 8px;
        color: #666;
        display: block;
    }

    .filter-control {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
    }

    .filter-control:focus {
        border-color: #00227c;
        outline: none;
        box-shadow: 0 0 0 3px rgba(0, 34, 124, 0.1);
    }

    .filter-actions {
        display: flex;
        gap: 10px;
    }

    .btn-filter {
        background: #00227c;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-filter:hover {
        background: #1e40af;
        transform: translateY(-2px);
    }

    .btn-reset {
        background: #6b7280;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .btn-reset:hover {
        background: #4b5563;
        transform: translateY(-2px);
        color: white;
    }

    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border-left: 5px solid #00227c;
        transition: var(--transition);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .stat-number {
        font-size: 32px;
        font-weight: 800;
        color: #00227c;
        display: block;
    }

    .stat-label {
        color: #666;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 5px;
    }

    .no-products {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .no-products i {
        font-size: 64px;
        color: #ccc;
        margin-bottom: 20px;
        display: block;
    }

    .no-products h5 {
        color: #666;
        margin: 0 0 10px 0;
        font-size: 18px;
    }

    .no-products p {
        color: #999;
        margin: 0;
        font-size: 14px;
    }

    @media (max-width: 768px) {
        .product-grid {
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .filter-form {
            flex-direction: column;
        }
        
        .filter-group {
            min-width: 100%;
        }

        .filter-actions {
            width: 100%;
            justify-content: stretch;
        }

        .btn-filter, .btn-reset {
            flex: 1;
        }
    }

    @media (max-width: 480px) {
        .product-grid {
            grid-template-columns: 1fr;
        }

        .stats-cards {
            grid-template-columns: repeat(2, 1fr);
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
        <a href="index.php" class="nav-btn">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="data_barang.php" class="nav-btn active">
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
        <a href="history_admin.php" class="nav-btn">
            <i class="fas fa-history"></i> History
        </a>
    </div>

    <div class="content-container">
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
            <div class="stat-card">
                <span class="stat-number"><?php echo number_format($total_products); ?></span>
                <div class="stat-label">Total Produk</div>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo number_format($low_stock_count); ?></span>
                <div class="stat-label">Stok Menipis</div>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo number_format($out_of_stock_count); ?></span>
                <div class="stat-label">Stok Habis</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters">
            <div class="filter-title">
                <i class="fas fa-filter"></i> Filter Produk
            </div>
            <form class="filter-form" method="GET">
                <div class="filter-group">
                    <label class="filter-label">Kategori</label>
                    <select name="kategori" class="filter-control">
                        <option value="">Semua Kategori</option>
                        <?php 
                        if ($categories) {
                            mysqli_data_seek($categories, 0);
                            while ($category = mysqli_fetch_assoc($categories)): 
                        ?>
                            <option value="<?php echo $category['id']; ?>" 
                                    <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['nama']); ?>
                            </option>
                        <?php 
                            endwhile; 
                        }
                        ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Cari Produk</label>
                    <input type="text" name="search" class="filter-control" 
                           placeholder="Nama produk..." 
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn-filter">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="data_barang.php" class="btn-reset">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Action Buttons -->
        <div class="mb-4">
            <a href="form-tambah.php" class="btn-add">
                <i class="fas fa-plus-circle"></i> Tambah Produk Baru
            </a>
        </div>

        <!-- Products Grid -->
        <?php if ($products && mysqli_num_rows($products) > 0): ?>
            <div class="product-grid">
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
                    <div class="product-card">
                        <?php if (!empty($product_image) && file_exists('uploads/' . $product_image)): ?>
                            <img src="uploads/<?php echo htmlspecialchars($product_image); ?>" 
                                 alt="<?php echo htmlspecialchars($product_name); ?>" 
                                 class="product-image">
                        <?php else: ?>
                            <div class="product-image" style="background: linear-gradient(135deg, #f0f0f0 0%, #e0e0e0 100%); display: flex; align-items: center; justify-content: center; color: #999;">
                                <i class="fas fa-image" style="font-size: 48px;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="product-content">
                            <div class="product-category">
                                <?php echo htmlspecialchars($product_category); ?>
                            </div>
                            <div class="product-name">
                                <?php echo htmlspecialchars($product_name); ?>
                            </div>
                            <div class="product-price">
                                Rp <?php echo number_format($product_price, 0, ',', '.'); ?>
                            </div>
                            <?php if (!empty($product_description)): ?>
                            <div class="product-description">
                                <?php echo htmlspecialchars($product_description); ?>
                            </div>
                            <?php endif; ?>
                            <div class="product-stock">
                                <i class="fas fa-boxes"></i>
                                Stok: <?php echo number_format($product_stock); ?>
                                <span class="stock-badge <?php echo $stock_class; ?>">
                                    <?php echo $stock_status; ?>
                                </span>
                            </div>
                            <div class="product-actions">
                                <a href="form_ubah.php?id=<?php echo $product_id; ?>" class="btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="barang_delete.php?id=<?php echo $product_id; ?>" 
                                   class="btn-delete" 
                                   data-product-name="<?php echo htmlspecialchars($product_name); ?>">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-products">
                <i class="fas fa-box-open"></i>
                <h5>
                    <?php if (isset($_GET['search']) || isset($_GET['kategori'])): ?>
                        Tidak ada produk yang sesuai dengan filter
                    <?php else: ?>
                        Belum ada produk
                    <?php endif; ?>
                </h5>
                <p>
                    <?php if (isset($_GET['search']) || isset($_GET['kategori'])): ?>
                        Coba ubah filter pencarian atau tambahkan produk baru
                    <?php else: ?>
                        Produk akan muncul di sini setelah ditambahkan
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Enhanced delete confirmation
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productName = this.getAttribute('data-product-name');
            const deleteUrl = this.href;
            
            // Create custom confirmation dialog
            const confirmDialog = document.createElement('div');
            confirmDialog.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.7);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 10000;
                backdrop-filter: blur(5px);
            `;
            
            confirmDialog.innerHTML = `
                <div style="
                    background: white;
                    padding: 30px;
                    border-radius: 15px;
                    text-align: center;
                    max-width: 400px;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                    margin: 20px;
                ">
                    <div style="
                        width: 60px;
                        height: 60px;
                        background: #fee2e2;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin: 0 auto 20px;
                    ">
                        <i class="fas fa-exclamation-triangle" style="color: #ef4444; font-size: 24px;"></i>
                    </div>
                    <h3 style="margin-bottom: 15px; color: #333;">Konfirmasi Hapus Produk</h3>
                    <p style="margin-bottom: 10px; color: #666;">Apakah Anda yakin ingin menghapus produk:</p>
                    <p style="margin-bottom: 25px; color: #333; font-weight: 600;">"${productName}"</p>
                    <p style="margin-bottom: 25px; color: #ef4444; font-size: 14px;">Tindakan ini tidak dapat dibatalkan!</p>
                    <div style="display: flex; gap: 10px; justify-content: center;">
                        <button id="cancelDelete" style="
                            padding: 10px 20px;
                            border: 2px solid #ddd;
                            background: white;
                            color: #666;
                            border-radius: 8px;
                            cursor: pointer;
                            font-weight: 500;
                            transition: all 0.3s ease;
                        ">Batal</button>
                        <button id="confirmDelete" style="
                            padding: 10px 20px;
                            border: none;
                            background: #ef4444;
                            color: white;
                            border-radius: 8px;
                            cursor: pointer;
                            font-weight: 500;
                            transition: all 0.3s ease;
                        ">Ya, Hapus Produk</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(confirmDialog);
            
            // Handle cancel
            confirmDialog.querySelector('#cancelDelete').addEventListener('click', () => {
                document.body.removeChild(confirmDialog);
            });
            
            // Handle confirm
            confirmDialog.querySelector('#confirmDelete').addEventListener('click', () => {
                document.body.removeChild(confirmDialog);
                showLoading();
                window.location.href = deleteUrl;
            });
            
            // Close on backdrop click
            confirmDialog.addEventListener('click', (e) => {
                if (e.target === confirmDialog) {
                    document.body.removeChild(confirmDialog);
                }
            });
        });
    });

    // Add loading animation for actions
    document.querySelectorAll('.btn-edit, .btn-add').forEach(button => {
        button.addEventListener('click', function() {
            showLoading();
        });
    });

    // Filter form loading
    document.querySelector('.filter-form').addEventListener('submit', function() {
        showLoading();
    });

    // Auto-submit filter form when category changes
    document.querySelector('select[name="kategori"]').addEventListener('change', function() {
        this.form.submit();
    });
</script>

<?php include 'include/admin_footer.php'; ?>
