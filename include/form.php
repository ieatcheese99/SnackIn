<?php include 'header.php';

$data_barang = select("SELECT barang.*, kategori.nama AS kategori_nama 
                       FROM barang 
                       JOIN kategori ON barang.kategori_id = kategori.id");

?>

<div class="container mt-5">
    <h1>Data Barang</h1>
    <hr>
    <a href="form-tambah.php" class="btn btn-primary mb-1"> Tambah </a>


    <table class="table table-bordered table-striped mt-3">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Deskripsi</th>
                <th>Stok</th>
                <th>Harga</th>
                <th>Gambar</th>
                <th>Kategori</th> <!-- ✅ Tambahkan kolom kategori -->
                <th>Opsi</th>
            </tr>
        </thead>


        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($data_barang as $barang) : ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= $barang['nama']; ?></td>
                    <td><?= $barang['Deskripsi']; ?></td>
                    <td><?= $barang['Stok']; ?></td>
                    <td>Rp. <?= number_format($barang['Harga'], 0, ',', ','); ?></td>
                    <td><img src="<?= $barang['gambar']; ?>" width="100" alt="Gambar Barang"></td>
                    <td><?= $barang['kategori_nama']; ?></td> <!-- ✅ Perbaikan kategori -->
                    <td>
                        <a href="form_ubah.php?id=<?= $barang['id'] ?>" class="btn btn-warning">Edit</a>
                        <a href="form_delete.php?id=<?= $barang['id'] ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?');">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>

    </table>
</div>

<!-- Option 1: Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>


</body>

</html>