<?php
// Koneksi ke database
include 'config/database.php';

// Fungsi untuk menambahkan barang
function create_barang($post, $file)
{
    global $db;

    $Nama = $post['nama'];
    $Deskripsi = $post['deskripsi'];
    $Stok = $post['stok'];
    $Harga = $post['harga'];
    $KategoriID = $post['kategori_id']; // Ambil kategori dari form

    // Validasi kategori (pastikan tidak kosong)
    if (empty($KategoriID)) {
        return false;
    }

    // Handle file upload
    if (isset($file['gambar']) && $file['gambar']['error'] == 0) {
        $gambar_name = $file['gambar']['name'];
        $gambar_tmp = $file['gambar']['tmp_name'];
        $gambar_path = 'uploads/' . $gambar_name; // Pastikan folder 'uploads' ada

        if (move_uploaded_file($gambar_tmp, $gambar_path)) {
            // File berhasil diupload
        } else {
            return false;
        }
    } else {
        return false;
    }

    // Prepare the SQL statement
    $query = "INSERT INTO barang (nama, deskripsi, stok, harga, gambar, kategori_id) 
              VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'ssiisi', $Nama, $Deskripsi, $Stok, $Harga, $gambar_path, $KategoriID);

    if (mysqli_stmt_execute($stmt)) {
        return true;
    } else {
        return false;
    }
}

// Cek apakah form telah disubmit
if (isset($_POST['submit'])) {
    if (create_barang($_POST, $_FILES)) {
        echo "<script>
                alert('Data berhasil ditambahkan!');
                document.location.href = 'index.php';
              </script>";
    } else {
        echo "<script>
                alert('Data gagal ditambahkan! Periksa input dan coba lagi.');
                document.location.href = 'tambah_barang.php';
              </script>";
    }
}
?>

<!doctype html>
<html lang="id">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <!-- Bootstrap 5 CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <title>Tambah Barang</title>
</head>

<body>
    <div class="container mt-5">
        <h1>Tambah Barang</h1>

        <!-- Form untuk menambah barang -->
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group mb-3 mt-4">
                <label for="nama">
                    <h5>Nama Barang:</h5>
                </label>
                <input type="text" class="form-control" name="nama" id="nama" placeholder="Nama Barang" required>
            </div>

            <div class="form-group mb-3">
                <label for="deskripsi">Deskripsi:</label>
                <textarea class="form-control" name="deskripsi" id="deskripsi" placeholder="Deskripsi Barang" required></textarea>
            </div>

            <div class="form-group mb-3">
                <label for="stok">Stok:</label>
                <input type="number" class="form-control" name="stok" id="stok" placeholder="Stok Barang" required>
            </div>

            <div class="form-group mb-3">
                <label for="harga">Harga:</label>
                <input type="number" class="form-control" name="harga" id="harga" placeholder="Harga Barang" required>
            </div>

            <div class="form-group mb-3">
                <label for="kategori_id">Kategori:</label>
                <select class="form-control" name="kategori_id" id="kategori_id" required>
                    <option value="">Pilih Kategori</option>
                    <?php
                    // Ambil daftar kategori dari database
                    $query = "SELECT id, nama FROM kategori";
                    $result = mysqli_query($db, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='{$row['id']}'>{$row['nama']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="gambar">Gambar Barang:</label>
                <input type="file" class="form-control" name="gambar" id="gambar" required>
            </div>

            <button type="submit" name="submit" class="btn btn-primary">Tambah</button>
        </form>
    </div>

    <?php include 'include/footer.php'; ?>
</body>

</html>