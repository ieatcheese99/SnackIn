<?php
include 'database.php';

// Fungsi menampilkan data
function select($query)
{
    global $db;

    $result = mysqli_query($db, $query);
    $rows = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

// Fungsi tambah barang
function create_barang($post) {
    global $db;

    $Nama_Barang    = $post['nama'];
    $Deskripsi      = $post['deskripsi'];
    $Stok           = $post['stok'];
    $Harga          = $post['harga'];
    
    // Handle file upload
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $gambar_name = $_FILES['gambar']['name'];
        $gambar_tmp = $_FILES['gambar']['tmp_name'];
        $gambar_path = 'uploads/' . $gambar_name; // Ensure 'uploads' directory exists

        if (move_uploaded_file($gambar_tmp, $gambar_path)) {
            // File uploaded successfully
        } else {
            return false; // Failed to move uploaded file
        }
    } else {
        return false; // No file uploaded or error in file upload
    }

    // Prepare the SQL statement to prevent SQL injection
    $query = "INSERT INTO barang (nama, deskripsi, stok, harga, gambar) 
              VALUES (?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'ssiis', $Nama, $Deskripsi, $Stok, $Harga, $gambar_path);

    mysqli_stmt_execute($stmt);

    return mysqli_affected_rows($db);
}
?>