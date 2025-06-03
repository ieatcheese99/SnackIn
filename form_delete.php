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

// Koneksi ke database
include 'config/database.php';

// Cek apakah ID ada
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Ambil data barang sebelum dihapus untuk konfirmasi
    $query = "SELECT * FROM barang WHERE id=?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $barang = mysqli_fetch_assoc($result);

    if ($barang) {
        // Hapus gambar jika ada
        if (!empty($barang['gambar'])) {
            $image_path = '';
            // Handle both cases for image path
            if (strpos($barang['gambar'], 'uploads/') === 0) {
                $image_path = $barang['gambar'];
            } else {
                $image_path = 'uploads/' . $barang['gambar'];
            }
            
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        // Hapus data dari database
        $delete_query = "DELETE FROM barang WHERE id=?";
        $delete_stmt = mysqli_prepare($db, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, "i", $id);
        
        if (mysqli_stmt_execute($delete_stmt)) {
            // Log admin action
            if (function_exists('logAdminAction')) {
                logAdminAction("Delete Product", "ID: $id, Name: " . $barang['nama']);
            }
            
            $success_message = "Produk '" . htmlspecialchars($barang['nama']) . "' berhasil dihapus!";
        } else {
            $error_message = "Gagal menghapus produk!";
        }
    } else {
        $error_message = "Produk tidak ditemukan!";
    }
} else {
    $error_message = "ID produk tidak valid!";
}

$page_title = "Hapus Produk";
include 'include/admin_header.php';
?>

<style>
    :root {
        --orange-primary: #ff9800;
        --orange-secondary: #f57c00;
        --orange-light: #ffb74d;
        --orange-dark: #e65100;
    }

    .delete-container {
        background: white;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        padding: 40px;
        margin: 20px auto;
        max-width: 600px;
        text-align: center;
    }

    .delete-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        margin: 0 auto 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        animation: pulse 2s infinite;
    }

    .success-icon {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .error-icon {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .delete-title {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 20px;
        color: #333;
    }

    .delete-message {
        font-size: 18px;
        margin-bottom: 30px;
        line-height: 1.6;
    }

    .success-message {
        color: #059669;
    }

    .error-message {
        color: #dc2626;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-back {
        background: var(--orange-primary);
        color: white;
        padding: 15px 30px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        font-size: 16px;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-back:hover {
        background: var(--orange-secondary);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(255, 152, 0, 0.3);
        color: white;
    }

    .btn-add-new {
        background: #10b981;
        color: white;
        padding: 15px 30px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        font-size: 16px;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-add-new:hover {
        background: #059669;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
        color: white;
    }

    .progress-bar {
        width: 100%;
        height: 4px;
        background: #f0f0f0;
        border-radius: 2px;
        margin: 30px 0;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: var(--orange-primary);
        border-radius: 2px;
        animation: progress 3s ease-in-out;
    }

    @keyframes progress {
        0% { width: 0%; }
        100% { width: 100%; }
    }

    .redirect-info {
        font-size: 14px;
        color: #666;
        margin-top: 20px;
    }

    .page-header {
        background: linear-gradient(135deg, var(--orange-primary) 0%, var(--orange-secondary) 100%);
        color: white;
        padding: 40px 0;
        margin-bottom: 30px;
    }

    .page-title {
        font-size: 32px;
        font-weight: 700;
        margin: 0;
    }

    .page-subtitle {
        font-size: 16px;
        opacity: 0.9;
        margin: 5px 0 0 0;
    }

    @media (max-width: 768px) {
        .delete-container {
            margin: 10px;
            padding: 30px 20px;
        }
        
        .delete-title {
            font-size: 24px;
        }
        
        .delete-message {
            font-size: 16px;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .btn-back, .btn-add-new {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1 class="page-title"><i class="fas fa-trash-alt"></i> Hapus Produk</h1>
        <p class="page-subtitle">Proses penghapusan produk dari sistem</p>
    </div>
</div>

<div class="container-fluid">
    <div class="delete-container">
        <?php if (isset($success_message)): ?>
            <div class="delete-icon success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h1 class="delete-title">Berhasil Dihapus!</h1>
            <div class="delete-message success-message">
                <?php echo $success_message; ?>
            </div>
            
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
            
            <div class="action-buttons">
                <a href="data_barang.php" class="btn-back" onclick="showLoading()">
                    <i class="fas fa-arrow-left"></i> Kembali ke Data Barang
                </a>
                <a href="form_tambah.php" class="btn-add-new" onclick="showLoading()">
                    <i class="fas fa-plus"></i> Tambah Produk Baru
                </a>
            </div>
            
            <div class="redirect-info">
                <i class="fas fa-info-circle"></i> Anda akan diarahkan kembali dalam 3 detik...
            </div>
            
        <?php else: ?>
            <div class="delete-icon error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h1 class="delete-title">Gagal Menghapus!</h1>
            <div class="delete-message error-message">
                <?php echo $error_message ?? 'Terjadi kesalahan yang tidak diketahui.'; ?>
            </div>
            
            <div class="action-buttons">
                <a href="data_barang.php" class="btn-back" onclick="showLoading()">
                    <i class="fas fa-arrow-left"></i> Kembali ke Data Barang
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Auto redirect after successful deletion
    <?php if (isset($success_message)): ?>
    setTimeout(function() {
        showLoading();
        window.location.href = 'data_barang.php';
    }, 3000);
    <?php endif; ?>

    // Add loading to all navigation buttons
    document.querySelectorAll('a').forEach(function(link) {
        link.addEventListener('click', function() {
            showLoading();
        });
    });
</script>

<?php include 'include/admin_footer.php'; ?>
