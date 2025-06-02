<?php
// Define admin access constant
define('ADMIN_ACCESS', true);

// Cek akses admin
require_once 'config/security.php';
requireAdmin();

require "config/database.php";

// Fungsi untuk menambahkan barang
function create_barang($post, $file) {
    global $db;

    $Nama = sanitize_input($post['nama']);
    $Deskripsi = sanitize_input($post['deskripsi']);
    $Stok = (int)$post['stok'];
    $Harga = (int)$post['harga'];
    $KategoriID = (int)$post['kategori_id'];

    // Validasi kategori
    if (empty($KategoriID)) {
        return false;
    }

    // Handle file upload
    if (isset($file['gambar']) && $file['gambar']['error'] == 0) {
        $gambar_name = $file['gambar']['name'];
        $gambar_tmp = $file['gambar']['tmp_name'];
        $gambar_path = 'uploads/' . time() . '_' . $gambar_name;

        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }

        if (move_uploaded_file($gambar_tmp, $gambar_path)) {
            // File berhasil diupload
        } else {
            return false;
        }
    } else {
        return false;
    }

    // Prepare the SQL statement
    $query = "INSERT INTO barang (nama, Deskripsi, Stok, Harga, gambar, kategori_id) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'ssiisi', $Nama, $Deskripsi, $Stok, $Harga, $gambar_path, $KategoriID);

    if (mysqli_stmt_execute($stmt)) {
        logAdminAction("Add Product", $Nama);
        return true;
    } else {
        return false;
    }
}

// Cek apakah form telah disubmit
if (isset($_POST['submit'])) {
    if (create_barang($_POST, $_FILES)) {
        $success_message = "Data berhasil ditambahkan!";
    } else {
        $error_message = "Data gagal ditambahkan! Periksa input dan coba lagi.";
    }
}

$page_title = "Tambah Barang";
include 'include/admin_header.php';
?>

<style>
    .form-container {
        background: white;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        padding: 40px;
        margin: 20px auto;
        max-width: 800px;
    }

    .form-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .form-header h1 {
        color: #ff9800;
        font-weight: 700;
        margin-bottom: 10px;
        font-size: 28px;
    }

    .form-header p {
        color: #666;
        margin: 0;
    }

    .form-control, .form-select {
        border-radius: 12px;
        border: 2px solid #e9ecef;
        padding: 15px 20px;
        font-size: 16px;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }

    .form-control:focus, .form-select:focus {
        border-color: #ff9800;
        box-shadow: 0 0 0 4px rgba(255,152,0,0.1);
        background: white;
    }

    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .btn-primary {
        background: #ff9800;
        border: none;
        padding: 15px 30px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 16px;
        transition: all 0.3s ease;
        width: 100%;
    }

    .btn-primary:hover {
        background: #f57c00;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(255,152,0,0.3);
    }

    .btn-secondary {
        background: #6c757d;
        border: none;
        padding: 15px 30px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 16px;
        transition: all 0.3s ease;
        color: white;
        text-decoration: none;
        display: inline-block;
        text-align: center;
        width: 100%;
        margin-top: 10px;
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-2px);
        color: white;
    }

    .alert {
        border-radius: 12px;
        border: none;
        padding: 15px 20px;
        margin-bottom: 25px;
        font-weight: 600;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
    }

    .alert-danger {
        background: #f8d7da;
        color: #721c24;
    }

    .input-group {
        position: relative;
    }

    .input-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #666;
        z-index: 10;
    }

    .form-control.with-icon {
        padding-left: 45px;
    }

    #image-preview {
        max-width: 200px;
        max-height: 200px;
        margin-top: 10px;
        border-radius: 12px;
        border: 2px solid #e9ecef;
        display: none;
    }

    @media (max-width: 768px) {
        .form-container {
            margin: 10px;
            padding: 30px 20px;
        }
        
        .form-header h1 {
            font-size: 24px;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1 class="page-title"><i class="fas fa-plus-circle"></i> Tambah Barang</h1>
        <p class="page-subtitle">Tambah produk baru ke inventory</p>
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

    <div class="form-container">
        <div class="form-header">
            <h1><i class="fas fa-plus-circle"></i> Tambah Barang Baru</h1>
            <p>Isi form di bawah untuk menambah produk baru</p>
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

        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <label for="nama" class="form-label">
                            <i class="fas fa-tag"></i> Nama Barang
                        </label>
                        <div class="input-group">
                            <i class="fas fa-tag input-icon"></i>
                            <input type="text" class="form-control with-icon" name="nama" id="nama" 
                                   placeholder="Masukkan nama barang" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="harga" class="form-label">
                            <i class="fas fa-money-bill"></i> Harga
                        </label>
                        <div class="input-group">
                            <i class="fas fa-money-bill input-icon"></i>
                            <input type="number" class="form-control with-icon" name="harga" id="harga" 
                                   placeholder="Masukkan harga" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="stok" class="form-label">
                            <i class="fas fa-boxes"></i> Stok
                        </label>
                        <div class="input-group">
                            <i class="fas fa-boxes input-icon"></i>
                            <input type="number" class="form-control with-icon" name="stok" id="stok" 
                                   placeholder="Masukkan jumlah stok" required>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-4">
                        <label for="kategori_id" class="form-label">
                            <i class="fas fa-list"></i> Kategori
                        </label>
                        <div class="input-group">
                            <i class="fas fa-list input-icon"></i>
                            <select class="form-select with-icon" name="kategori_id" id="kategori_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php
                                $categories = mysqli_query($db, "SELECT * FROM kategori ORDER BY nama");
                                while ($category = mysqli_fetch_assoc($categories)):
                                ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['nama']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="gambar" class="form-label">
                            <i class="fas fa-image"></i> Gambar Produk
                        </label>
                        <div class="input-group">
                            <i class="fas fa-image input-icon"></i>
                            <input type="file" class="form-control with-icon" name="gambar" id="gambar" 
                                   accept="image/*" required>
                        </div>
                        <img id="image-preview" alt="Preview">
                    </div>
                </div>
                
                <div class="col-12">
                    <div class="mb-4">
                        <label for="deskripsi" class="form-label">
                            <i class="fas fa-align-left"></i> Deskripsi
                        </label>
                        <textarea class="form-control" name="deskripsi" id="deskripsi" rows="4" 
                                  placeholder="Masukkan deskripsi produk" required></textarea>
                    </div>
                </div>
            </div>
            
            <button type="submit" name="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Barang
            </button>
            
            <a href="data_barang.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </form>
    </div>
</div>

<script>
    // Preview image before upload
    document.getElementById('gambar').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('image-preview');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const nama = document.getElementById('nama').value.trim();
        const harga = document.getElementById('harga').value;
        const stok = document.getElementById('stok').value;
        const kategori = document.getElementById('kategori_id').value;
        const gambar = document.getElementById('gambar').files[0];
        
        if (!nama || !harga || !stok || !kategori || !gambar) {
            e.preventDefault();
            alert('Semua field harus diisi!');
            return false;
        }
        
        if (harga <= 0 || stok < 0) {
            e.preventDefault();
            alert('Harga harus lebih dari 0 dan stok tidak boleh negatif!');
            return false;
        }
    });

    // Auto redirect after success
    <?php if (isset($success_message)): ?>
    setTimeout(function() {
        window.location.href = 'data_barang.php';
    }, 2000);
    <?php endif; ?>
</script>

<?php include 'include/admin_footer.php'; ?>
