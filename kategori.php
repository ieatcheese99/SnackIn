<?php
// Define admin access constant
define('ADMIN_ACCESS', true);

// Cek akses admin
require_once 'config/security.php';
requireAdmin();

require "config/database.php";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                if (isset($_POST['nama']) && !empty(trim($_POST['nama']))) {
                    $nama = sanitize_input($_POST['nama']);
                    $query = "INSERT INTO kategori (nama) VALUES (?)";
                    $stmt = mysqli_prepare($db, $query);
                    mysqli_stmt_bind_param($stmt, 's', $nama);

                    if (mysqli_stmt_execute($stmt)) {
                        $success_message = "Kategori berhasil ditambahkan!";
                        logAdminAction("Add Category", $nama);
                    } else {
                        $error_message = "Gagal menambahkan kategori!";
                    }
                }
                break;

            case 'edit':
                if (isset($_POST['id'], $_POST['nama']) && !empty(trim($_POST['nama']))) {
                    $id = (int) $_POST['id'];
                    $nama = sanitize_input($_POST['nama']);
                    $query = "UPDATE kategori SET nama = ? WHERE id = ?";
                    $stmt = mysqli_prepare($db, $query);
                    mysqli_stmt_bind_param($stmt, 'si', $nama, $id);

                    if (mysqli_stmt_execute($stmt)) {
                        $success_message = "Kategori berhasil diperbarui!";
                        logAdminAction("Edit Category", "ID: $id, New Name: $nama");
                    } else {
                        $error_message = "Gagal memperbarui kategori!";
                    }
                }
                break;

            case 'delete':
                if (isset($_POST['id'])) {
                    $id = (int) $_POST['id'];

                    // Check if category has products
                    $check_query = "SELECT COUNT(*) as count FROM barang WHERE kategori_id = ?";
                    $check_stmt = mysqli_prepare($db, $check_query);
                    mysqli_stmt_bind_param($check_stmt, 'i', $id);
                    mysqli_stmt_execute($check_stmt);
                    $check_result = mysqli_stmt_get_result($check_stmt);
                    $product_count = mysqli_fetch_assoc($check_result)['count'];

                    if ($product_count > 0) {
                        $error_message = "Tidak dapat menghapus kategori yang masih memiliki produk!";
                    } else {
                        $query = "DELETE FROM kategori WHERE id = ?";
                        $stmt = mysqli_prepare($db, $query);
                        mysqli_stmt_bind_param($stmt, 'i', $id);

                        if (mysqli_stmt_execute($stmt)) {
                            $success_message = "Kategori berhasil dihapus!";
                            logAdminAction("Delete Category", "ID: $id");
                        } else {
                            $error_message = "Gagal menghapus kategori!";
                        }
                    }
                }
                break;
        }
    }
}

// Get all categories with product count
$query = "SELECT k.*, COUNT(b.id) as product_count 
          FROM kategori k 
          LEFT JOIN barang b ON k.id = b.kategori_id 
          GROUP BY k.id 
          ORDER BY k.nama";
$result = mysqli_query($db, $query);

$page_title = "Kategori";
include 'include/admin_header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1 class="page-title"><i class="fas fa-tags"></i> Manajemen Kategori</h1>
        <p class="page-subtitle">Kelola kategori produk untuk mengorganisir toko Anda</p>
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
        <a href="kategori.php" class="nav-btn active">
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

    <div class="content-container content-animate">
        <!-- Alert Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Add Category Button -->
        <div class="text-center">
            <button type="button" class="add-category-btn" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="fas fa-plus"></i> Tambah Kategori Baru
            </button>
        </div>

        <!-- Categories Grid -->
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <div class="category-grid">
                <?php while ($category = mysqli_fetch_assoc($result)): ?>
                    <div class="category-card animate-item">
                        <div class="category-header">
                            <h3 class="category-name"><?php echo htmlspecialchars($category['nama']); ?></h3>
                            <span class="product-count">
                                <?php echo $category['product_count']; ?> produk
                            </span>
                        </div>
                        <div class="category-actions">
                            <button type="button" class="btn-edit" data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                                data-id="<?php echo $category['id']; ?>"
                                data-name="<?php echo htmlspecialchars($category['nama']); ?>">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button type="button" class="btn-delete" data-bs-toggle="modal"
                                data-bs-target="#deleteCategoryModal" data-id="<?php echo $category['id']; ?>"
                                data-name="<?php echo htmlspecialchars($category['nama']); ?>"
                                data-count="<?php echo $category['product_count']; ?>">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-tags"></i>
                <h3>Belum Ada Kategori</h3>
                <p>Mulai dengan menambahkan kategori pertama untuk mengorganisir produk Anda.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Tambah Kategori Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" id="nama" name="nama" required
                            placeholder="Masukkan nama kategori...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label for="edit_nama" class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" id="edit_nama" name="nama" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Category Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-trash"></i> Hapus Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle text-warning"
                            style="font-size: 3rem; margin-bottom: 20px;"></i>
                        <h4>Apakah Anda yakin?</h4>
                        <p id="delete_message">Kategori ini akan dihapus secara permanen.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" id="delete_submit">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Edit Category Modal
    document.getElementById('editCategoryModal').addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');

        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nama').value = name;
    });

    // Delete Category Modal
    document.getElementById('deleteCategoryModal').addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');
        const count = parseInt(button.getAttribute('data-count'));

        document.getElementById('delete_id').value = id;

        const messageElement = document.getElementById('delete_message');
        const submitButton = document.getElementById('delete_submit');

        if (count > 0) {
            messageElement.innerHTML = `<strong>Peringatan!</strong><br>Kategori "<strong>${name}</strong>" memiliki <strong>${count} produk</strong>.<br>Anda tidak dapat menghapus kategori yang masih memiliki produk.`;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-ban"></i> Tidak Dapat Dihapus';
        } else {
            messageElement.innerHTML = `Kategori "<strong>${name}</strong>" akan dihapus secara permanen.<br>Tindakan ini tidak dapat dibatalkan.`;
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-trash"></i> Hapus';
        }
    });

    // Auto hide alerts
    document.addEventListener('DOMContentLoaded', function () {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function (alert) {
            setTimeout(function () {
                alert.style.opacity = '0';
                setTimeout(function () {
                    alert.remove();
                }, 300);
            }, 5000);
        });
    });
</script>
</body>

</html>

<style>
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
        --transition-fast: 0.2s ease;
        --transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: var(--light-bg);
        color: var(--text-dark);
        min-height: 100vh;
    }

    /* Header Styles - Same as user_ui.php */
    .main-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: var(--white);
        padding: 15px 0;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: var(--shadow-md);
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
        font-family: 'Outfit', sans-serif;
        font-size: 24px;
        font-weight: 800;
        text-decoration: none;
        color: var(--white);
        letter-spacing: -0.5px;
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
        transition: var(--transition);
        position: relative;
        color: var(--white);
        text-decoration: none;
        font-size: 15px;
    }

    .main-nav a:hover {
        color: var(--accent-color);
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
        transition: var(--transition);
        color: var(--white);
        text-decoration: none;
    }

    .action-icon:hover {
        color: var(--accent-color);
        transform: translateY(-2px);
    }

    .logout-icon {
        color: var(--danger-color);
    }

    .mobile-menu-toggle {
        display: none;
        font-size: 24px;
        cursor: pointer;
    }

    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: var(--white);
        padding: 40px 0;
        margin-bottom: 30px;
        text-align: center;
        box-shadow: var(--shadow-md);
    }

    .page-title {
        font-family: 'Outfit', sans-serif;
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 10px;
        letter-spacing: -1px;
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
        background: var(--white);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        color: var(--primary-color);
    }

    .nav-btn.active {
        background: var(--primary-color);
        color: var(--white);
    }

    /* Content Container */
    .content-container {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        padding: 30px;
        margin: 20px;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    /* Alert Styles */
    .alert {
        border-radius: 15px;
        border: none;
        padding: 15px 20px;
        margin-bottom: 20px;
        font-weight: 500;
    }

    .alert-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .alert-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    /* Category Grid */
    .category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .category-card {
        background: var(--white);
        border-radius: var(--radius-md);
        padding: 25px;
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
        border: 1px solid rgba(0, 0, 0, 0.05);
        position: relative;
        overflow: hidden;
    }

    .category-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: var(--primary-color);
    }

    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
    }

    .category-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .category-name {
        font-family: 'Outfit', sans-serif;
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
    }

    .product-count {
        background: rgba(0, 34, 124, 0.1);
        color: var(--primary-color);
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 700;
    }

    .category-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px dashed rgba(0, 0, 0, 0.1);
    }

    .btn-edit,
    .btn-delete {
        padding: 10px 16px;
        border: none;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        width: 50%;
        justify-content: center;
    }

    .btn-edit {
        background: #fef3c7;
        color: #d97706;
    }

    .btn-edit:hover {
        background: #fde68a;
        transform: translateY(-2px);
    }

    .btn-delete {
        background: #fee2e2;
        color: #dc2626;
    }

    .btn-delete:hover {
        background: #fecaca;
        transform: translateY(-2px);
    }

    /* Add Category Button */
    .add-category-btn {
        background: var(--primary-color);
        color: var(--white);
        border: none;
        padding: 15px 30px;
        border-radius: var(--radius-md);
        font-weight: 700;
        font-family: 'Outfit', sans-serif;
        font-size: 16px;
        cursor: pointer;
        transition: var(--transition);
        box-shadow: 0 4px 15px rgba(0, 34, 124, 0.2);
        margin-bottom: 30px;
    }

    .add-category-btn:hover {
        background: var(--secondary-color);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 34, 124, 0.3);
        color: var(--white);
    }

    /* Modal Styles */
    .modal-content {
        border-radius: var(--radius-lg);
        border: none;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%) !important;
        padding: 20px 25px;
        border-bottom: none;
    }

    .modal-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        color: var(--white);
    }

    .btn-close {
        filter: invert(1);
    }

    .modal-body {
        padding: 30px;
    }

    .form-control {
        border: 2px solid #e2e8f0;
        border-radius: var(--radius-md);
        padding: 12px 15px;
        font-size: 15px;
        transition: var(--transition);
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(0, 34, 124, 0.1);
    }

    .btn-primary {
        background: var(--primary-color);
        border: none;
        padding: 12px 25px;
        border-radius: var(--radius-md);
        font-weight: 600;
        transition: var(--transition);
        box-shadow: 0 4px 10px rgba(0, 34, 124, 0.2);
    }

    .btn-primary:hover {
        background: var(--secondary-color);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0, 34, 124, 0.3);
        color: var(--white);
    }

    .btn-secondary {
        background: #f1f5f9;
        color: var(--text-dark);
        border: none;
        padding: 12px 25px;
        border-radius: var(--radius-md);
        font-weight: 600;
        transition: var(--transition);
    }

    .btn-secondary:hover {
        background: #e2e8f0;
        transform: translateY(-2px);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6b7280;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-state h3 {
        font-size: 1.5rem;
        margin-bottom: 10px;
        color: #374151;
    }

    .empty-state p {
        font-size: 1rem;
        max-width: 400px;
        margin: 0 auto;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .main-nav {
            display: none;
        }

        .mobile-menu-toggle {
            display: block;
        }

        .category-grid {
            grid-template-columns: 1fr;
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
    }
</style>