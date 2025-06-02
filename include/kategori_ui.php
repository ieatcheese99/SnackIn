<?php
require "config/database.php";

// Handle form submissions
if (isset($_POST['tambah_kategori'])) {
    $nama = $_POST['nama'];
    
    $query = "INSERT INTO kategori (nama) VALUES (?)";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 's', $nama);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<div class='alert alert-success'>Kategori berhasil ditambahkan!</div>";
    } else {
        echo "<div class='alert alert-danger'>Gagal menambahkan kategori!</div>";
    }
}

if (isset($_POST['edit_kategori'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    
    $query = "UPDATE kategori SET nama = ? WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'si', $nama, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<div class='alert alert-success'>Kategori berhasil diupdate!</div>";
    } else {
        echo "<div class='alert alert-danger'>Gagal mengupdate kategori!</div>";
    }
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    // Check if category is being used by products
    $check_query = "SELECT COUNT(*) as count FROM barang WHERE kategori_id = ?";
    $check_stmt = mysqli_prepare($db, $check_query);
    mysqli_stmt_bind_param($check_stmt, 'i', $id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $check_data = mysqli_fetch_assoc($check_result);
    
    if ($check_data['count'] > 0) {
        echo "<div class='alert alert-warning'>Kategori tidak dapat dihapus karena masih digunakan oleh produk!</div>";
    } else {
        $query = "DELETE FROM kategori WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<div class='alert alert-success'>Kategori berhasil dihapus!</div>";
        } else {
            echo "<div class='alert alert-danger'>Gagal menghapus kategori!</div>";
        }
    }
}

// Get all categories with product count
$categories = mysqli_query($db, "SELECT k.*, COUNT(b.id) as product_count FROM kategori k LEFT JOIN barang b ON k.id = b.kategori_id GROUP BY k.id ORDER BY k.nama");
?>

<style>
.kategori-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 25px;
    margin: 20px 0;
}

.kategori-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.kategori-title {
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

.kategori-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.kategori-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.kategori-card:hover {
    border-color: #00227c;
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 34, 124, 0.1);
}

.kategori-name {
    font-size: 18px;
    font-weight: 600;
    color: #00227c;
    margin-bottom: 10px;
}

.kategori-count {
    color: #666;
    font-size: 14px;
    margin-bottom: 15px;
}

.kategori-actions {
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

@media (max-width: 768px) {
    .kategori-grid {
        grid-template-columns: 1fr;
    }
    
    .kategori-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
}
</style>

<div class="kategori-container">
    <div class="kategori-header">
        <h2 class="kategori-title">Manajemen Kategori</h2>
        <button class="btn-tambah" onclick="toggleForm()">
            <i class="fas fa-plus"></i> Tambah Kategori
        </button>
    </div>

    <!-- Form Tambah/Edit Kategori -->
    <div id="kategoriForm" class="form-container" style="display: none;">
        <h4 id="formTitle">Tambah Kategori Baru</h4>
        <form method="POST" id="categoryForm">
            <input type="hidden" name="id" id="kategoriId">
            
            <div class="form-group">
                <label for="nama" class="form-label">Nama Kategori</label>
                <input type="text" class="form-control" name="nama" id="nama" required>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" name="tambah_kategori" id="submitBtn" class="btn-submit">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <button type="button" class="btn-edit" onclick="cancelForm()">
                    <i class="fas fa-times"></i> Batal
                </button>
            </div>
        </form>
    </div>

    <!-- Daftar Kategori -->
    <div class="kategori-grid">
        <?php if (mysqli_num_rows($categories) > 0): ?>
            <?php while ($category = mysqli_fetch_assoc($categories)): ?>
            <div class="kategori-card">
                <div class="kategori-name"><?php echo htmlspecialchars($category['nama']); ?></div>
                <div class="kategori-count">
                    <i class="fas fa-box"></i> <?php echo $category['product_count']; ?> produk
                </div>
                <div class="kategori-actions">
                    <button class="btn-edit" onclick="editKategori(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['nama']); ?>')">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <?php if ($category['product_count'] == 0): ?>
                    <a href="?hapus=<?php echo $category['id']; ?>" class="btn-hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
                        <i class="fas fa-trash"></i> Hapus
                    </a>
                    <?php else: ?>
                    <button class="btn-hapus" disabled title="Tidak dapat dihapus karena masih ada produk">
                        <i class="fas fa-lock"></i> Terkunci
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                <i class="fas fa-folder-open" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                <p style="color: #666; margin: 0;">Belum ada kategori. Tambahkan kategori pertama Anda!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleForm() {
    const form = document.getElementById('kategoriForm');
    const isVisible = form.style.display !== 'none';
    
    if (isVisible) {
        form.style.display = 'none';
    } else {
        form.style.display = 'block';
        resetForm();
    }
}

function resetForm() {
    document.getElementById('categoryForm').reset();
    document.getElementById('kategoriId').value = '';
    document.getElementById('formTitle').textContent = 'Tambah Kategori Baru';
    document.getElementById('submitBtn').name = 'tambah_kategori';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Simpan';
}

function editKategori(id, nama) {
    document.getElementById('kategoriForm').style.display = 'block';
    document.getElementById('kategoriId').value = id;
    document.getElementById('nama').value = nama;
    document.getElementById('formTitle').textContent = 'Edit Kategori';
    document.getElementById('submitBtn').name = 'edit_kategori';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Update';
}

function cancelForm() {
    document.getElementById('kategoriForm').style.display = 'none';
    resetForm();
}
</script>
