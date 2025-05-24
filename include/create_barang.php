<?php
// Koneksi ke database
include 'config/database.php';

// Fungsi untuk menambahkan barang
function create_barang($post, $file) {
    global $db;
    
    $Nama = $post['nama'];
    $Deskripsi = $post['deskripsi'];
    $Stok = $post['stok'];
    $Harga = $post['harga'];
    
    // Handle file upload
    if (isset($file['gambar']) && $file['gambar']['error'] == 0) {
        $gambar_name = $file['gambar']['name'];
        $gambar_tmp = $file['gambar']['tmp_name'];
        $gambar_path = 'uploads/' . $gambar_name; // Pastikan folder 'uploads' ada
        
        // Pindahkan file gambar ke folder 'uploads'
        if (move_uploaded_file($gambar_tmp, $gambar_path)) {
            // File berhasil diupload
        } else {
            return false; // Gagal memindahkan file
        }
    } else {
        return false; // Tidak ada gambar yang diupload atau ada error
    }

    // Prepare the SQL statement to prevent SQL injection
    $query = "INSERT INTO barang (nama, deskripsi, stok, harga, gambar) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'ssiis', $Nama, $Deskripsi, $Stok, $Harga, $gambar_path);

    if (mysqli_stmt_execute($stmt)) {
        return true; // Barang berhasil ditambahkan
    } else {
        return false; // Gagal menambahkan barang
    }
}

// Cek apakah form telah disubmit
if (isset($_POST['submit'])) {
    if (create_barang($_POST, $_FILES)) {
        echo "Barang berhasil ditambahkan!";
    } else {
        echo "Gagal menambahkan barang.";
    }
}
?>