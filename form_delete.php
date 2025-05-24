<?php
// Koneksi ke database
include 'config/database.php';

// Cek apakah ID ada
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Ambil data barang sebelum dihapus
    $query = "SELECT gambar FROM barang WHERE id=?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $barang = mysqli_fetch_assoc($result);

    if ($barang) {
        // Hapus gambar jika ada
        if (!empty($barang['gambar']) && file_exists($barang['gambar'])) {
            unlink($barang['gambar']);
        }

        // Hapus data dari database
        $query = "DELETE FROM barang WHERE id=?";
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
    } else {
        echo "<script>alert('Barang tidak ditemukan!');</script>";
    }
}
?>
