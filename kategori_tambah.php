
<?php
// Koneksi ke database
include 'config/database.php';

// Fungsi untuk menambahkan kategori
function create_kategori($post) {
    global $db;
    
    $Nama = $post['nama'];

    // Prepare the SQL statement to prevent SQL injection
    $query = "INSERT INTO kategori (nama) VALUES (?)";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 's', $Nama);

    if (mysqli_stmt_execute($stmt)) {
        return true;
    } else {
        return false;
    }
}

// Cek apakah form telah disubmit
if (isset($_POST['submit'])) {
    if (create_kategori($_POST)) {
        echo "<script>
                alert('Data berhasil ditambahkan!');
                document.location.href = 'index.php';
              </script>";
    } else {
        echo "<script>
                alert('Data gagal ditambahkan!');
                document.location.href = 'index.php';
              </script>";
    }
}
?>



<?php include 'include/header.php';



?>


<div class="container mt-5">
    <h1>Tambah Kategori</h1>
    <hr>


    <!-- Form untuk menambah kategori -->
    

    <form method="post">
        <div class="form-group mb-3 mt-4">
            <label for="nama">
                <h5>Nama Kategori</h5>
            </label>
            <input type="text" class="form-control" name="nama" id="nama" placeholder="Masukkan Nama Kategori" required style="border-width: 2px">
        </div>

        <button type="submit" name="submit" class="btn btn-primary" style="float: right;">Tambah</button>
        
    </form>
    
</div>


<?php include 'include/footer.php'; ?>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>


</body>

</html>