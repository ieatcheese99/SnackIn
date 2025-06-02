<?php
require "config/database.php";

// Handle form submissions
if (isset($_POST['tambah_barang'])) {
    $nama = $_POST['nama'];
    $harga = $_POST['Harga']; // Updated column name
    $kategori_id = $_POST['kategori_id'];
    $deskripsi = $_POST['Deskripsi']; // Updated column name
    $stok = $_POST['Stok']; // Added stock field
    
    // Handle file upload
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
            $gambar = $target_file;
        }
    }
    
    $query = "INSERT INTO barang (nama, Harga, kategori_id, Deskripsi, Stok, gambar) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'siisis', $nama, $harga, $kategori_id, $deskripsi, $stok, $gambar);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<div class='alert alert-success'>Produk berhasil ditambahkan!</div>";
    } else {
        echo "<div class='alert alert-danger'>Gagal menambahkan produk!</div>";
    }
}

if (isset($_POST['edit_barang'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $harga = $_POST['Harga']; // Updated column name
    $kategori_id = $_POST['kategori_id'];
    $deskripsi = $_POST['Deskripsi']; // Updated column name
    $stok = $_POST['Stok']; // Added stock field
    
    // Handle file upload
    $gambar_query = '';
    $gambar_param = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
            $gambar_query = ', gambar = ?';
            $gambar_param = $target_file;
        }
    }
    
    if ($gambar_param) {
        $query = "UPDATE barang SET nama = ?, Harga = ?, kategori_id = ?, Deskripsi = ?, Stok = ?" . $gambar_query . " WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'siisisi', $nama, $harga, $kategori_id, $deskripsi, $stok, $gambar_param, $id);
    } else {
        $query = "UPDATE barang SET nama = ?, Harga = ?, kategori_id = ?, Deskripsi = ?, Stok = ? WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'siisii', $nama, $harga, $kategori_id, $deskripsi, $stok, $id);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<div class='alert alert-success'>Produk berhasil diupdate!</div>";
    } else {
        echo "<div class='alert alert-danger'>Gagal mengupdate produk!</div>";
    }
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    // Get image path to delete file
    $get_image = mysqli_query($db, "SELECT gambar FROM barang WHERE id = $id");
    $image_data = mysqli_fetch_assoc($get_image);
    
    $query = "DELETE FROM barang WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Delete image file if exists
        if ($image_data['gambar'] && file_exists($image_data['gambar'])) {
            unlink($image_data['gambar']);
        }
        echo "<div class='alert alert-success'>Produk berhasil dihapus!</div>";
    } else {
        echo "<div class='alert alert-danger'>Gagal menghapus produk!</div>";
    }
}

// Get all products with category names
$products = mysqli_query($db, "SELECT b.*, k.nama as kategori_nama FROM barang b LEFT JOIN kategori k ON b.kategori_id = k.id ORDER BY b.id DESC");

// Get all categories for dropdown
$categories = mysqli_query($db, "SELECT * FROM kategori ORDER BY nama");
?>

<style>
.products-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 25px;
    margin: 20px 0;
}

.products-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.products-title {
    font-size: 24px;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.btn-tambah {
    background: linear-gradient(45deg, #00227c, #1e40af);
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-tambah:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 34, 124, 0.3);
    color: white;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.product-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.product-card:hover {
    border-color: #00227c;
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 34, 124, 0.1);
}

.product-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 15px;
}

.product-name {
    font-size: 18px;
    font-weight: 600;
    color: #00227c;
    margin-bottom: 8px;
}

.product-price {
    font-size: 16px;
    font-weight: 600;
    color: #28a745;
    margin-bottom: 8px;
}

.product-stock {
    font-size: 14px;
    color: #6c757d;
    margin-bottom: 8px;
}

.product-category {
    font-size: 12px;
    background: #e9ecef;
    color: #495057;
    padding: 4px 8px;
    border-radius: 12px;
    display: inline-block;
    margin-bottom: 10px;
}

.product-description {
    color: #666;
    font-size: 14px;
    margin-bottom: 15px;
    line-height: 1.5;
}

.product-actions {
    display: flex;
    gap: 10px;
}

.btn-edit, .btn-hapus {
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.btn-edit {
    background: #ffc107;
    color: #212529;
}

.btn-edit:hover {
    background: #e0a800;
    color: #212529;
}

.btn-hapus {
    background: #dc3545;
    color: white;
}

.btn-hapus:hover {
    background: #c82333;
    color: white;
}

.form-container {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 20px;
    border: 2px solid #e9ecef;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 10px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #00227c;
    box-shadow: 0 0 0 3px rgba(0, 34, 124, 0.1);
}

.btn-submit {
    background: linear-gradient(45deg, #28a745, #20c997);
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
}

.stock-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.stock-low {
    background: #f8d7da;
    color: #721c24;
}

.stock-medium {
    background: #fff3cd;
    color: #856404;
}

.stock-high {
    background: #d4edda;
    color: #155724;
}

@media (max-width: 768px) {
    .products-grid {
        grid-template-columns: 1fr;
    }
    
    .products-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="products-container">
    <div class="products-header">
        <h2 class="products-title">Manajemen Produk</h2>
        <button class="btn-tambah" onclick="toggleForm()">
            <i class="fas fa-plus"></i> Tambah Produk
        </button>
    </div>

    <!-- Form Tambah/Edit Produk -->
    <div id="productForm" class="form-container" style="display: none;">
        <h4 id="formTitle">Tambah Produk Baru</h4>
        <form method="POST" enctype="multipart/form-data" id="barangForm">
            <input type="hidden" name="id" id="productId">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="nama" class="form-label">Nama Produk</label>
                    <input type="text" class="form-control" name="nama" id="nama" required>
                </div>
                
                <div class="form-group">
                    <label for="Harga" class="form-label">Harga</label>
                    <input type="number" class="form-control" name="Harga" id="Harga" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="kategori_id" class="form-label">Kategori</label>
                    <select class="form-control" name="kategori_id" id="kategori_id" required>
                        <option value="">Pilih Kategori</option>
                        <?php 
                        mysqli_data_seek($categories, 0);
                        while ($category = mysqli_fetch_assoc($categories)): 
                        ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['nama']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="Stok" class="form-label">Stok</label>
                    <input type="number" class="form-control" name="Stok" id="Stok" required min="0">
                </div>
            </div>
            
            <div class="form-group">
                <label for="gambar" class="form-label">Gambar Produk</label>
                <input type="file" class="form-control" name="gambar" id="gambar" accept="image/*">
            </div>
            
            <div class="form-group">
                <label for="Deskripsi" class="form-label">Deskripsi</label>
                <textarea class="form-control" name="Deskripsi" id="Deskripsi" rows="3"></textarea>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" name="tambah_barang" id="submitBtn" class="btn-submit">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <button type="button" class="btn-edit" onclick="cancelForm()">
                    <i class="fas fa-times"></i> Batal
                </button>
            </div>
        </form>
    </div>

    <!-- Daftar Produk -->
    <div class="products-grid">
        <?php if (mysqli_num_rows($products) > 0): ?>
            <?php while ($product = mysqli_fetch_assoc($products)): ?>
            <div class="product-card">
                <?php if ($product['gambar']): ?>
                <img src="<?php echo htmlspecialchars($product['gambar']); ?>" alt="<?php echo htmlspecialchars($product['nama']); ?>" class="product-image">
                <?php else: ?>
                <div class="product-image" style="background: #e9ecef; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                    <i class="fas fa-image" style="font-size: 48px;"></i>
                </div>
                <?php endif; ?>
                
                <div class="product-name"><?php echo htmlspecialchars($product['nama']); ?></div>
                <div class="product-price">Rp <?php echo number_format($product['Harga'], 0, ',', '.'); ?></div>
                
                <div class="product-stock">
                    Stok: 
                    <span class="stock-badge <?php 
                        if ($product['Stok'] <= 5) echo 'stock-low';
                        elseif ($product['Stok'] <= 20) echo 'stock-medium';
                        else echo 'stock-high';
                    ?>">
                        <?php echo $product['Stok']; ?> unit
                    </span>
                </div>
                
                <div class="product-category"><?php echo htmlspecialchars($product['kategori_nama'] ?? 'Tanpa Kategori'); ?></div>
                <div class="product-description">
                    <?php echo htmlspecialchars(substr($product['Deskripsi'] ?? '', 0, 100)); ?>
                    <?php if (strlen($product['Deskripsi'] ?? '') > 100) echo '...'; ?>
                </div>
                <div class="product-actions">
                    <button class="btn-edit" onclick="editProduct(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['nama']); ?>', <?php echo $product['Harga']; ?>, <?php echo $product['kategori_id']; ?>, '<?php echo htmlspecialchars($product['Deskripsi']); ?>', <?php echo $product['Stok']; ?>)">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <a href="?hapus=<?php echo $product['id']; ?>" class="btn-hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                        <i class="fas fa-trash"></i> Hapus
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                <i class="fas fa-box-open" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                <p style="color: #666; margin: 0;">Belum ada produk. Tambahkan produk pertama Anda!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleForm() {
    const form = document.getElementById('productForm');
    const isVisible = form.style.display !== 'none';
    
    if (isVisible) {
        form.style.display = 'none';
    } else {
        form.style.display = 'block';
        resetForm();
    }
}

function resetForm() {
    document.getElementById('barangForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('formTitle').textContent = 'Tambah Produk Baru';
    document.getElementById('submitBtn').name = 'tambah_barang';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Simpan';
}

function editProduct(id, nama, harga, kategori_id, deskripsi, stok) {
    document.getElementById('productForm').style.display = 'block';
    document.getElementById('productId').value = id;
    document.getElementById('nama').value = nama;
    document.getElementById('Harga').value = harga;
    document.getElementById('kategori_id').value = kategori_id;
    document.getElementById('Deskripsi').value = deskripsi;
    document.getElementById('Stok').value = stok;
    document.getElementById('formTitle').textContent = 'Edit Produk';
    document.getElementById('submitBtn').name = 'edit_barang';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Update';
}

function cancelForm() {
    document.getElementById('productForm').style.display = 'none';
    resetForm();
}
</script>
