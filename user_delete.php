<?php
session_start();
require "config/database.php";

// Simple admin check
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    // Cek apakah user yang akan dihapus bukan user yang sedang login
    $current_user_query = "SELECT username FROM user WHERE id = ?";
    $current_stmt = mysqli_prepare($db, $current_user_query);
    mysqli_stmt_bind_param($current_stmt, 'i', $id);
    mysqli_stmt_execute($current_stmt);
    $current_result = mysqli_stmt_get_result($current_stmt);
    $current_user = mysqli_fetch_assoc($current_result);

    if ($current_user && $current_user['username'] === $_SESSION['username']) {
        echo "<script>
                alert('Anda tidak dapat menghapus akun Anda sendiri!');
                document.location.href = 'user.php';
              </script>";
        exit();
    }

    // Cek apakah user dengan ID tersebut ada di database
    $checkQuery = "SELECT * FROM user WHERE id = ?";
    $checkStmt = mysqli_prepare($db, $checkQuery);
    mysqli_stmt_bind_param($checkStmt, "i", $id);
    mysqli_stmt_execute($checkStmt);
    $result = mysqli_stmt_get_result($checkStmt);

    if (mysqli_num_rows($result) > 0) {
        $user_data = mysqli_fetch_assoc($result);
        if ($user_data['level'] === 'admin') {
            echo "<script>
                    alert('User dengan level admin tidak dapat dihapus!');
                    document.location.href = 'user.php';
                  </script>";
            exit();
        }

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
            echo "<script>
                    alert('Gagal menghapus user!');
                    document.location.href = 'user.php';
                  </script>";
        }
    } else {
        echo "<script>
                alert('User tidak ditemukan!');
                document.location.href = 'user.php';
              </script>";
    }
} else {
    echo "<script>
            alert('ID tidak valid!');
            document.location.href = 'user.php';
          </script>";
}
?>