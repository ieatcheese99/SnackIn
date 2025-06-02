<?php
// Define admin access constant
define('ADMIN_ACCESS', true);

// Cek akses admin
require_once 'config/security.php';
requireAdmin();

require "config/database.php";

// Ambil data barang berdasarkan ID
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
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
    $Stok = (int)$_POST['stok'];
    $Harga = (int)$_POST['harga'];
    $KategoriID = (int)$_POST['kategori_id'];

    // Cek apakah ada gambar baru yang diupload
    if ($_FILES['gambar']['error'] == 0) {
        $gambar_name = $_FILES['gambar']['name'];
        $gambar_tmp = $_FILES['gambar']['tmp_name'];
        $gambar_path = 'uploads/' . time() . '_' . $gambar_name;

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
    } else {
        // Update tanpa mengubah gambar
        $query = "UPDATE barang SET nama=?, Deskripsi=?, Stok=?, Harga=?, kategori_id=? WHERE id=?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "ssiiii", $Nama, $Deskripsi, $Stok, $Harga, $KategoriID, $id);
    }

    if (!isset($error_message) && mysqli_stmt_execute($stmt)) {
        $success_message = "Data berhasil diperbarui!";
        logAdminAction("Update Product", "ID: $id, Name: $Nama");
        
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

    .current-image {
        width: 150px;
        height: 150px;
        border-radius: 12px;
        object-fit: cover;
        margin: 0 auto 20px;
        display: block;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
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
        <h1 class="page-title"><i class="fas fa-edit"></i> Edit Barang</h1>
        <p class="page-subtitle">Edit informasi produk: <?php echo htmlspecialchars($barang['nama']); ?></p>
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
            <img src="<?php echo htmlspecialchars($barang['gambar']); ?>" alt="Current Image" class="current-image">
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
                                   value="<?php echo htmlspecialchars($barang['nama']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="harga" class="form-label">
                            <i class="fas fa-money-bill"></i> Harga
                        </label>
                        <div class="input-group">
                            <i class="fas fa-money-bill input-icon"></i>
                            <input type="number" class="form-control with-icon" name="harga" id="harga" 
                                   value="<?php echo $barang['Harga']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="stok" class="form-label">
                            <i class="fas fa-boxes"></i> Stok
                        </label>
                        <div class="input-group">
                            <i class="fas fa-boxes input-icon"></i>
                            <input type="number" class="form-control with-icon" name="stok" id="stok" 
                                   value="<?php echo $barang['Stok']; ?>" required>
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
                                    $selected = ($barang['kategori_id'] == $category['id']) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($category['nama']); ?>
                                    </option>
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
                            <input type="file" class="form-control with-icon" name="gambar" id="gambar" accept="image/*">
                        </div>
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah gambar</small>
                        <img id="image-preview" alt="Preview">
                    </div>
                </div>
                
                <div class="col-12">
                    <div class="mb-4">
                        <label for="deskripsi" class="form-label">
                            <i class="fas fa-align-left"></i> Deskripsi
                        </label>
                        <textarea class="form-control" name="deskripsi" id="deskripsi" rows="4" required><?php echo htmlspecialchars($barang['Deskripsi']); ?></textarea>
                    </div>
                </div>
            </div>
            
            <button type="submit" name="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Perubahan
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
        
        if (!nama || !harga || !stok || !kategori) {
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
