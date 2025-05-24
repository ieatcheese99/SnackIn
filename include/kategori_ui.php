<?php include 'header.php';

$data_kategori = select("SELECT * FROM kategori");
?>

<div class="container mt-5">
    <h1>List Kategori</h1>
    <hr>
    <a href="kategori_tambah.php" class="btn btn-primary mb-1"> Tambah </a>
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Kategori</th>
                <th>Opsi</th>
            </tr>
        </thead>

        <tbody>
        <?php $no = 1; ?>
        <?php foreach ($data_kategori as $kategori) : ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= $kategori['nama']; ?></td> 
                <td width="15%" class="text-center">
                    <a href="kategori_ubah.php?id=<?= $kategori['id'] ?>" class="btn btn-success">Ubah</a>
                    <a href="kategori_delete.php?id=<?= $kategori['id'] ?>" class="btn btn-danger" 
                       onclick="return confirm('Yakin ingin menghapus kategori ini?');">Hapus</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
        crossorigin="anonymous"></script>
</body>
</html>
