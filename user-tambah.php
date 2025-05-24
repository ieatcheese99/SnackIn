<?php
// Koneksi ke database
include 'config/database.php';

// Fungsi untuk menambahkan user
function create_user($post) {
    global $db;
    
    $username = $post['username'];
    $password = password_hash($post['password'], PASSWORD_DEFAULT); // Hash password
    $level = $post['level'];
    
    // Prepare the SQL statement to prevent SQL injection
    $query = "INSERT INTO user (username, password, level) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'sss', $username, $password, $level);

    if (mysqli_stmt_execute($stmt)) {
        return true; // User berhasil ditambahkan
    } else {
        return false; // Gagal menambahkan user
    }
}

// Cek apakah form telah disubmit
if (isset($_POST['submit'])) {
    if (create_user($_POST)) {
        echo "<script>
                alert('User berhasil ditambahkan!');
                document.location.href = 'index.php';
              </script>";
    } else {
        echo "<script>
                alert('User gagal ditambahkan!');
                document.location.href = 'index.php';
              </script>";
    }
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
    <h1>Tambah User</h1>

    <!-- Form untuk menambah user -->
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group mb-3 mt-4">
            <label for="username">
                <h5>Username:</h5>
            </label>
            <input type="text" class="form-control" name="username" id="username" placeholder="Username" required>
        </div>

        <div class="form-group mb-3">
            <label for="password">Password:</label>
            <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
        </div>

        <div class="form-group mb-3">
            <label for="level">Level:</label>
            <select class="form-control" name="level" id="level" required>
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <button type="submit" name="submit" class="btn btn-primary" style="float: right;">Tambah</button>
    </form>
</div>

<div class="appFooter">
            <div class="footer-title">
                Copyright © Finapp 2021. All Rights Reserved.
            </div>
            Bootstrap 5 based mobile template.
        </div>
        <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

</body>
</html>
