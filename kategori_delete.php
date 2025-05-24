<?php
// Koneksi ke database
include 'config/database.php';

// Pastikan ID tersedia
if (isset($_GET['id'])) {
    $id = $_GET['id'];


        // Hapus data dari database
        $query = "DELETE FROM kategori WHERE id=?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>
                    alert('Data berhasil dihapus!');
                    document.location.href = 'index.php';
                  </script>";
        } else {
            echo "<script>alert('Gagal menghapus data!');</script>";
        }
    }
?>
