<?php
define('ADMIN_ACCESS', true);

require_once 'config/database.php';
require_once 'config/functions.php';

session_start();
requireAdmin();

// Get category ID from URL
$id = (int)$_GET['id'];
$query = "SELECT * FROM kategori WHERE id = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$category = mysqli_fetch_assoc($result);

if (!$category) {
    header("Location: kategori.php");
    exit();
}

// Handle form submission
if (isset($_POST['submit'])) {
    $nama = sanitize_input($_POST['nama']);
    
    // Check if category name already exists (excluding current category)
    $check_query = "SELECT COUNT(*) as count FROM kategori WHERE nama = ? AND id != ?";
    $check_stmt = mysqli_prepare($db, $check_query);
    mysqli_stmt_bind_param($check_stmt, 'si', $nama, $id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $check_data = mysqli_fetch_assoc($check_result);
    
    if ($check_data['count'] > 0) {
        $error_message = "Nama kategori sudah digunakan!";
    } else {
        $query = "UPDATE kategori SET nama = ? WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'si', $nama, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Kategori berhasil diperbarui!";
            logAdminAction("Edit Category", "ID: $id, New Name: $nama");
            // Update category data for display
            $category['nama'] = $nama;
        } else {
            $error_message = "Gagal mengupdate kategori!";
        }
    }
}

$page_title = "Edit Kategori";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Snack In Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/admin-style.css" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, #f97316, #ea580c); min-height: 100vh; display: flex; align-items: center; justify-content: center;">

<div class="form-container">
    <div class="form-header">
        <div class="form-icon">
            <i class="fas fa-tags"></i>
        </div>
        <h1 class="form-title">Edit Kategori</h1>
        <p class="form-subtitle">Edit kategori produk</p>
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

    <form method="POST" id="editCategoryForm">
        <div class="form-group">
            <label for="nama" class="form-label">
                <i class="fas fa-tags"></i> Nama Kategori
            </label>
            <div class="input-group">
                <i class="fas fa-tags input-icon"></i>
                <input type="text" class="form-control with-icon" name="nama" id="nama" 
                       placeholder="Masukkan nama kategori" value="<?php echo htmlspecialchars($category['nama']); ?>" required minlength="2">
            </div>
        </div>

        <div style="margin-top: 32px;">
            <button type="submit" name="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 12px;">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
            
            <a href="kategori.php" class="btn btn-secondary" style="width: 100%;">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Form validation
    document.getElementById('editCategoryForm').addEventListener('submit', function(e) {
        const nama = document.getElementById('nama').value.trim();
        
        if (!nama) {
            e.preventDefault();
            alert('Nama kategori harus diisi!');
            return false;
        }
        
        if (nama.length < 2) {
            e.preventDefault();
            alert('Nama kategori minimal 2 karakter!');
            return false;
        }
    });

    // Auto redirect after success
    <?php if (isset($success_message)): ?>
    setTimeout(function() {
        window.location.href = 'kategori.php';
    }, 2000);
    <?php endif; ?>
</script>

</body>
</html>
