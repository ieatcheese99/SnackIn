<?php
include 'config/database.php'; // Sesuaikan dengan konfigurasi koneksi database

// Pastikan ID tersedia
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Konversi ID menjadi integer untuk keamanan

    // Ambil data kategori berdasarkan ID
    $query = "SELECT * FROM kategori WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $kategori = mysqli_fetch_assoc($result);

    // Jika kategori tidak ditemukan, redirect kembali
    if (!$kategori) {
        echo "<script>
                alert('Kategori tidak ditemukan!');
                document.location.href = 'kategori.php';
              </script>";
        exit();
    }
} else {
    echo "<script>
            alert('ID tidak valid!');
            document.location.href = 'kategori.php';
          </script>";
    exit();
}

// Jika form disubmit
if (isset($_POST['ubah'])) {
    $nama = $_POST['nama'];

    // Gunakan prepared statement untuk update
    $query = "UPDATE kategori SET nama = ? WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "si", $nama, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Redirect ke halaman kategori setelah update berhasil
        header("Location: kategori.php");
        exit();
    } else {
        echo "<script>alert('Gagal memperbarui kategori!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ubah Kategori</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
</head>
<body>
<div class="container mt-5">
    <h1>Ubah Kategori</h1>
    <form method="POST">
        <label for="nama" class="form-label">Nama Kategori:</label>
        <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($kategori['nama']); ?>" required><br>
        
        <button type="submit" name="ubah" class="btn btn-primary">Simpan Perubahan</button>
        <a href="kategori.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>
