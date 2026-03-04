<?php
// Define admin access constant
define('ADMIN_ACCESS', true);

// Cek akses admin
require_once 'config/database.php';

session_start();

// Fungsi untuk memeriksa akses admin
function requireAdmin()
{
    if (!isset($_SESSION['username']) || $_SESSION['level'] !== 'admin') {
        header("Location: login.php");
        exit();
    }
}

requireAdmin();

// Fungsi untuk sanitize input
function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Ambil data barang berdasarkan ID
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $query = "SELECT * FROM barang WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $barang = mysqli_fetch_assoc($result);

    if (!$barang) {
        header("Location: data_barang.php");
        exit();
    }
} else {
    header("Location: data_barang.php");
    exit();
}

// Cek apakah form telah disubmit untuk update data
if (isset($_POST['submit'])) {
    $Nama = sanitize_input($_POST['nama']);
    $Deskripsi = sanitize_input($_POST['deskripsi']);
    $Stok = (int) $_POST['stok'];
    $Harga = (int) $_POST['harga'];
    $KategoriID = (int) $_POST['kategori_id'];

    // Cek apakah ada gambar baru yang diupload
    if ($_FILES['gambar']['error'] == 0) {
        $gambar_name = $_FILES['gambar']['name'];
        $gambar_tmp = $_FILES['gambar']['tmp_name'];
        $gambar_extension = strtolower(pathinfo($gambar_name, PATHINFO_EXTENSION));

        // Validasi ekstensi file
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($gambar_extension, $allowed_extensions)) {
            $error_message = "Format file tidak didukung! Gunakan JPG, JPEG, PNG, atau GIF.";
        } else {
            // Generate unique filename
            $gambar_filename = time() . '_' . uniqid() . '.' . $gambar_extension;
            $gambar_path = 'uploads/' . $gambar_filename;

            if (move_uploaded_file($gambar_tmp, $gambar_path)) {
                // Hapus gambar lama
                if (!empty($barang['gambar']) && file_exists($barang['gambar'])) {
                    unlink($barang['gambar']);
                }

                // Update data termasuk gambar
                $query = "UPDATE barang SET nama=?, Deskripsi=?, Stok=?, Harga=?, gambar=?, kategori_id=? WHERE id=?";
                $stmt = mysqli_prepare($db, $query);
                mysqli_stmt_bind_param($stmt, "ssiisii", $Nama, $Deskripsi, $Stok, $Harga, $gambar_path, $KategoriID, $id);
            } else {
                $error_message = "Gagal mengupload gambar!";
            }
        }
    } else {
        // Update tanpa mengubah gambar
        $query = "UPDATE barang SET nama=?, Deskripsi=?, Stok=?, Harga=?, kategori_id=? WHERE id=?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "ssiiii", $Nama, $Deskripsi, $Stok, $Harga, $KategoriID, $id);
    }

    if (!isset($error_message) && mysqli_stmt_execute($stmt)) {
        $success_message = "Data berhasil diperbarui!";

        // Update local data
        $barang['nama'] = $Nama;
        $barang['Deskripsi'] = $Deskripsi;
        $barang['Stok'] = $Stok;
        $barang['Harga'] = $Harga;
        $barang['kategori_id'] = $KategoriID;
        if (isset($gambar_path)) {
            $barang['gambar'] = $gambar_path;
        }
    } elseif (!isset($error_message)) {
        $error_message = "Gagal memperbarui data!";
    }
}

$page_title = "Edit Barang";
include 'include/admin_header.php';
?>

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
        line-height: 1.6;
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

    /* Form Container */
    .form-container {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        padding: 40px;
        margin: 20px auto;
        max-width: 800px;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .form-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .form-header h1 {
        font-family: 'Outfit', sans-serif;
        color: var(--primary-color);
        font-weight: 800;
        margin-bottom: 10px;
        font-size: 28px;
        letter-spacing: -0.5px;
    }

    .form-header p {
        color: var(--text-muted);
        margin: 0;
    }

    .current-image {
        width: 150px;
        height: 150px;
        border-radius: var(--radius-md);
        object-fit: cover;
        margin: 0 auto 20px;
        display: block;
        box-shadow: var(--shadow-sm);
        border: 3px solid var(--primary-color);
    }

    .form-control,
    .form-select {
        border-radius: var(--radius-md);
        border: 2px solid #e2e8f0;
        padding: 15px 20px;
        font-size: 15px;
        transition: var(--transition);
        background: var(--light-bg);
        color: var(--text-dark);
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(0, 34, 124, 0.1);
        background: var(--white);
        outline: none;
    }

    .form-label {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 8px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-label i {
        color: var(--primary-color);
        width: 16px;
    }

    /* Buttons */
    .btn-primary {
        background: var(--primary-color);
        border: none;
        padding: 15px 30px;
        border-radius: var(--radius-md);
        font-weight: 700;
        font-size: 15px;
        transition: var(--transition);
        width: 100%;
        color: var(--white);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
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
        padding: 15px 30px;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 15px;
        transition: var(--transition);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        margin-top: 10px;
    }

    .btn-secondary:hover {
        background: #e2e8f0;
        transform: translateY(-2px);
        color: var(--text-dark);
        text-decoration: none;
    }

    /* Alert Styles */
    .alert {
        border-radius: var(--radius-md);
        border: none;
        padding: 15px 20px;
        margin-bottom: 25px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-success {
        background: #d1fae5;
        color: #059669;
    }

    .alert-danger {
        background: #fee2e2;
        color: #dc2626;
    }

    .input-group {
        position: relative;
    }

    .input-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--primary-color);
        z-index: 10;
        font-size: 16px;
    }

    .form-control.with-icon,
    .form-select.with-icon {
        padding-left: 45px;
    }

    #image-preview {
        max-width: 200px;
        max-height: 200px;
        margin-top: 15px;
        border-radius: var(--radius-md);
        border: 2px solid var(--primary-color);
        display: none;
        box-shadow: var(--shadow-sm);
    }

    .text-muted {
        color: var(--text-muted) !important;
        font-size: 13px;
        margin-top: 5px;
        display: block;
    }

    /* Enhanced Form Styling */
    .mb-4 {
        margin-bottom: 1.5rem;
    }

    .row {
        margin: 0 -15px;
    }

    .col-md-6,
    .col-12 {
        padding: 0 15px;
    }

    /* Loading Animation */
    .btn-loading {
        position: relative;
        pointer-events: none;
    }

    .btn-loading::after {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        margin: auto;
        border: 2px solid transparent;
        border-top-color: #ffffff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .form-container {
            margin: 10px;
            padding: 30px 20px;
        }

        .form-header h1 {
            font-size: 24px;
        }

        .nav-buttons {
            justify-content: center;
        }

        .nav-btn {
            padding: 10px 15px;
            font-size: 0.9rem;
        }

        .current-image {
            width: 120px;
            height: 120px;
        }

        .page-title {
            font-size: 2rem;
        }
    }

    @media (max-width: 576px) {
        .form-container {
            padding: 20px 15px;
        }

        .form-control,
        .form-select {
            padding: 12px 15px;
            font-size: 14px;
        }

        .form-control.with-icon,
        .form-select.with-icon {
            padding-left: 40px;
        }

        .input-icon {
            left: 12px;
            font-size: 14px;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1 class="page-title"><i class="fas fa-edit"></i> Edit Barang</h1>
        <p class="page-subtitle">Edit informasi produk: <?php echo htmlspecialchars($barang['nama']); ?></p>
    </div>
</div>

<div class="container-fluid">
    <!-- Navigation Buttons -->
    <div class="nav-buttons">
        <a href="index.php" class="nav-btn" onclick="showLoading()">
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

    <div class="form-container content-animate">
        <div class="form-header">
            <?php if (!empty($barang['gambar']) && file_exists($barang['gambar'])): ?>
                <img src="<?php echo htmlspecialchars($barang['gambar']); ?>" alt="Current Image" class="current-image">
            <?php else: ?>
                <div class="current-image"
                    style="background: linear-gradient(135deg, #f0f0f0 0%, #e0e0e0 100%); display: flex; align-items: center; justify-content: center; color: #999;">
                    <i class="fas fa-image" style="font-size: 48px;"></i>
                </div>
            <?php endif; ?>
            <h1><i class="fas fa-edit"></i> Edit Barang</h1>
            <p>Edit informasi produk: <strong><?php echo htmlspecialchars($barang['nama']); ?></strong></p>
        </div>

        <!-- Alert Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="editForm">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4 animate-item">
                        <label for="nama" class="form-label">
                            <i class="fas fa-tag"></i> Nama Barang
                        </label>
                        <div class="input-group">
                            <i class="fas fa-tag input-icon"></i>
                            <input type="text" class="form-control with-icon" name="nama" id="nama"
                                value="<?php echo htmlspecialchars($barang['nama']); ?>" required>
                        </div>
                    </div>

                    <div class="mb-4 animate-item">
                        <label for="harga" class="form-label">
                            <i class="fas fa-money-bill"></i> Harga
                        </label>
                        <div class="input-group">
                            <i class="fas fa-money-bill input-icon"></i>
                            <input type="number" class="form-control with-icon" name="harga" id="harga"
                                value="<?php echo $barang['Harga']; ?>" min="1" required>
                        </div>
                    </div>

                    <div class="mb-4 animate-item">
                        <label for="stok" class="form-label">
                            <i class="fas fa-boxes"></i> Stok
                        </label>
                        <div class="input-group">
                            <i class="fas fa-boxes input-icon"></i>
                            <input type="number" class="form-control with-icon" name="stok" id="stok"
                                value="<?php echo $barang['Stok']; ?>" min="0" required>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-4 animate-item">
                        <label for="kategori_id" class="form-label">
                            <i class="fas fa-list"></i> Kategori
                        </label>
                        <div class="input-group">
                            <i class="fas fa-list input-icon"></i>
                            <select class="form-select with-icon" name="kategori_id" id="kategori_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php
                                $categories = mysqli_query($db, "SELECT * FROM kategori ORDER BY nama");
                                if ($categories) {
                                    while ($category = mysqli_fetch_assoc($categories)):
                                        $selected = ($barang['kategori_id'] == $category['id']) ? 'selected' : '';
                                        ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo $selected; ?>>
                                            <?php echo htmlspecialchars($category['nama']); ?>
                                        </option>
                                    <?php
                                    endwhile;
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4 animate-item">
                        <label for="gambar" class="form-label">
                            <i class="fas fa-image"></i> Gambar Produk
                        </label>
                        <div class="input-group">
                            <i class="fas fa-image input-icon"></i>
                            <input type="file" class="form-control with-icon" name="gambar" id="gambar"
                                accept="image/*">
                        </div>
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah gambar. Format: JPG, JPEG, PNG,
                            GIF. Maksimal 5MB</small>
                        <img id="image-preview" alt="Preview">
                    </div>
                </div>

                <div class="col-12">
                    <div class="mb-4 animate-item">
                        <label for="deskripsi" class="form-label">
                            <i class="fas fa-align-left"></i> Deskripsi
                        </label>
                        <textarea class="form-control" name="deskripsi" id="deskripsi" rows="4"
                            required><?php echo htmlspecialchars($barang['Deskripsi']); ?></textarea>
                    </div>
                </div>
            </div>

            <button type="submit" name="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>

            <a href="data_barang.php" class="btn btn-secondary" onclick="showLoading()">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </form>
    </div>
</div>

<script>
    // Preview image before upload
    document.getElementById('gambar').addEventListener('change', function (e) {
        const file = e.target.files[0];
        const preview = document.getElementById('image-preview');

        if (file) {
            // Check file size (5MB limit)
            if (file.size > 5 * 1024 * 1024) {
                alert('Ukuran file terlalu besar! Maksimal 5MB.');
                this.value = '';
                preview.style.display = 'none';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    });

    // Form validation
    document.getElementById('editForm').addEventListener('submit', function (e) {
        const nama = document.getElementById('nama').value.trim();
        const harga = document.getElementById('harga').value;
        const stok = document.getElementById('stok').value;
        const kategori = document.getElementById('kategori_id').value;
        const deskripsi = document.getElementById('deskripsi').value.trim();

        if (!nama || !harga || !stok || !kategori || !deskripsi) {
            e.preventDefault();
            alert('Semua field harus diisi!');
            return false;
        }

        if (harga <= 0) {
            e.preventDefault();
            alert('Harga harus lebih dari 0!');
            return false;
        }

        if (stok < 0) {
            e.preventDefault();
            alert('Stok tidak boleh negatif!');
            return false;
        }

        // Show loading
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        submitBtn.disabled = true;

        // Show page loading
        showLoading();
    });

    // Auto redirect after success
    <?php if (isset($success_message)): ?>
        setTimeout(function () {
            showLoading();
            window.location.href = 'data_barang.php';
        }, 2000);
    <?php endif; ?>
</script>

<?php include 'include/admin_footer.php'; ?>