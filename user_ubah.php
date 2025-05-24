<?php
include 'config/database.php'; // Sesuaikan dengan konfigurasi koneksi database

// Ambil ID dari parameter URL
$id = $_GET['id'];
$query = "SELECT * FROM user WHERE id = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Jika form disubmit
if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $level = $_POST['level'];
    
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query = "UPDATE user SET username = ?, password = ?, level = ? WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'sssi', $username, $password, $level, $id);
    } else {
        $query = "UPDATE user SET username = ?, level = ? WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'ssi', $username, $level, $id);
    }
    
    mysqli_stmt_execute($stmt);
    
    // Redirect kembali ke halaman user
    header("Location: user.php");
    exit();
}
?>

<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" integrity="sha384-dpuaG1suU0eT09tx5plTaGMLBsfDLzUCCUXOY2j/LSvXYuG6Bqs43ALlhIqAJVRb" crossorigin="anonymous">

    <title>User Management</title>
</head>

<body>

<div class="container mt-5">
    <h1>Ubah User</h1>
    <hr>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" name="username" id="username" placeholder="Username" value="<?= htmlspecialchars($user['username']); ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">Password (kosongkan jika tidak ingin diubah)</label>
            <input type="password" class="form-control" name="password" id="password">
        </div>
        
        <div class="mb-3">
            <label for="level" class="form-label">Level</label>
            <select class="form-select" name="level" id="level" required>
                <option value="user" <?= ($user['level'] == 'user') ? 'selected' : ''; ?>>User</option>
                <option value="admin" <?= ($user['level'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
            </select>
        </div>
        
        <button type="submit" name="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="user.php" class="btn btn-secondary">Batal</a>
    </form>
</div>

</body>
</html>