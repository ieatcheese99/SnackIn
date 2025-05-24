<?php
include 'header.php';
include 'config/database.php'; // Pastikan koneksi ke database sudah disertakan

$data_user = select("SELECT * FROM user");
?>

<div class="container mt-5">
    <h1>Data User</h1>
    <hr>
    <a href="user-tambah.php" class="btn btn-primary mb-1">Tambah User</a>
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Username</th>
                <th>Password</th>
                <th>Level</th>
                <th>Opsi</th>
            </tr>
        </thead>
        <tbody>
        <?php $no = 1; ?>
        <?php foreach ($data_user as $user) : ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= htmlspecialchars($user['username']); ?></td>
                <td><?= htmlspecialchars($user['password']); ?></td>
                <td><?= htmlspecialchars($user['level']); ?></td>
                <td>
                    <a href="user_ubah.php?id=<?= $user['id'] ?>" class="btn btn-success">Ubah</a>
                    <a href="user_delete.php?id=<?= $user['id'] ?>" class="btn btn-danger" 
                       onclick="return confirm('Yakin ingin menghapus user ini?');">Hapus</a>
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
