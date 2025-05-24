<?php
include 'config/database.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    // Cek apakah user dengan ID tersebut ada di database
    $checkQuery = "SELECT * FROM user WHERE id = ?";
    $checkStmt = mysqli_prepare($db, $checkQuery);
    mysqli_stmt_bind_param($checkStmt, "i", $id);
    mysqli_stmt_execute($checkStmt);
    $result = mysqli_stmt_get_result($checkStmt);

    if (mysqli_num_rows($result) > 0) {
        // Hapus data dari database
        $query = "DELETE FROM user WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>
                    alert('User berhasil dihapus!');
                    document.location.href = 'user.php';
                  </script>";
        } else {
            echo "<script>alert('Gagal menghapus user!');</script>";
        }
    } else {
        echo "<script>alert('User tidak ditemukan!');</script>";
    }
} else {
    echo "<script>alert('ID tidak valid!');</script>";
}
?>