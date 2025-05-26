<?php
// File untuk mengecek akses admin
function checkAdminAccess() {
    // Start session jika belum
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Cek apakah user sudah login
    if (!isset($_SESSION['username'])) {
        header("Location: login.php?error=login_required");
        exit();
    }
    
    // Cek apakah user adalah admin
    if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'admin') {
        header("Location: user_ui.php?error=access_denied");
        exit();
    }
    
    return true;
}

// Function untuk redirect non-admin
function requireAdmin() {
    return checkAdminAccess();
}
?>
    