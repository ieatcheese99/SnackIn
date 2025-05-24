<?php
// Koneksi ke database
include 'config/database.php';

// Ambil data barang berdasarkan ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM barang WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $barang = mysqli_fetch_assoc($result);
}

// Cek apakah form telah disubmit untuk update data
if (isset($_POST['submit'])) {
    $Nama = $_POST['nama'];
    $Deskripsi = $_POST['deskripsi'];
    $Stok = $_POST['stok'];
    $Harga = $_POST['harga'];
    $KategoriID = $_POST['kategori_id'];

    // Cek apakah ada gambar baru yang diupload
    if ($_FILES['gambar']['error'] == 0) {
        $gambar_name = $_FILES['gambar']['name'];
        $gambar_tmp = $_FILES['gambar']['tmp_name'];
        $gambar_path = 'uploads/' . $gambar_name;

        if (move_uploaded_file($gambar_tmp, $gambar_path)) {
            // Update data termasuk gambar dan kategori
            $query = "UPDATE barang SET nama=?, deskripsi=?, stok=?, harga=?, gambar=?, kategori_id=? WHERE id=?";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "ssiisii", $Nama, $Deskripsi, $Stok, $Harga, $gambar_path, $KategoriID, $id);
        } else {
            echo "<script>alert('Gagal mengupload gambar!');</script>";
            exit;
        }
    } else {
        // Update tanpa mengubah gambar, tetapi kategori ikut diperbarui
        $query = "UPDATE barang SET nama=?, deskripsi=?, stok=?, harga=?, kategori_id=? WHERE id=?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "ssiisi", $Nama, $Deskripsi, $Stok, $Harga, $KategoriID, $id);
    }

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>
                alert('Data berhasil diperbarui!');
                document.location.href = 'index.php';
              </script>";
    } else {
        echo "<script>alert('Gagal memperbarui data!');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Barang</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h1>Edit Barang</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group mb-3">
                <label for="nama">Nama Barang:</label>
                <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($barang['nama']) ?>" required>
            </div>

            <div class="form-group mb-3">
                <label for="deskripsi">Deskripsi:</label>
                <textarea class="form-control" name="deskripsi" required><?= $barang['deskripsi'] ?? '' ?></textarea>
            </div>

            <div class="form-group mb-3">
                <label for="stok">Stok:</label>
                <input type="number" class="form-control" name="stok" value="<?= isset($barang['stok']) ? $barang['stok'] : '' ?>" required>
            </div>

            <div class="form-group mb-3">
                <label for="harga">Harga:</label>
                <input type="number" class="form-control" name="harga" value="<?= isset($barang['harga']) ? $barang['harga'] : '' ?>" required>
            </div>

            <div class="form-group mb-3">
                <label for="kategori_id">Kategori:</label>
                <select class="form-control" name="kategori_id" required>
                    <option value="">Pilih Kategori</option>
                    <?php
                    // Ambil daftar kategori dari database
                    $query = "SELECT id, nama FROM kategori";
                    $result = mysqli_query($db, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        $selected = ($barang['kategori_id'] == $row['id']) ? 'selected' : '';
                        echo "<option value='{$row['id']}' $selected>{$row['nama']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="gambar">Gambar Barang:</label>
                <input type="file" class="form-control" name="gambar">
                <br>
                <img src="<?= htmlspecialchars($barang['gambar']) ?>" width="100">
            </div>

            <button type="submit" name="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</body>

</html>