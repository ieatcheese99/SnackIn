<?php
define('ADMIN_ACCESS', true);

require_once 'config/database.php';

session_start();

// Fungsi sanitasi input
function sanitize_input($data)
{
    global $db;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($db, $data);
}

// Fungsi untuk memeriksa akses admin
function requireAdmin()
{
    if (!isset($_SESSION['username']) || $_SESSION['level'] !== 'admin') {
        header("Location: login.php");
        exit();
    }
}

// Cek akses admin
requireAdmin();

// Fungsi untuk menambahkan user
function create_user($post)
{
    global $db;

    $username = sanitize_input($post['username']);
    $password = password_hash($post['password'], PASSWORD_DEFAULT);
    $level = sanitize_input($post['level']);

    // Check if username already exists
    $check_query = "SELECT COUNT(*) as count FROM user WHERE username = ?";
    $check_stmt = mysqli_prepare($db, $check_query);
    mysqli_stmt_bind_param($check_stmt, 's', $username);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $check_data = mysqli_fetch_assoc($check_result);

    if ($check_data['count'] > 0) {
        return false; // Username already exists
    }

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
        $success_message = "User berhasil ditambahkan!";
    } else {
        $error_message = "User gagal ditambahkan! Username mungkin sudah ada.";
    }
}
$page_title = "Tambah User";
include 'include/admin_header.php';
?>

<style>
    .form-container {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        padding: 40px;
        width: 100%;
        max-width: 500px;
        margin: 20px;
        border: 1px solid rgba(0, 0, 0, 0.05);
        animation: fadeIn 0.5s ease;
    }

    .form-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .form-header h1 {
        font-family: 'Outfit', sans-serif;
        color: var(--primary-color);
        font-weight: 800;
        margin-bottom: 10px;
        font-size: 28px;
        letter-spacing: -0.5px;
    }

    .form-header p {
        color: var(--text-muted);
        margin: 0;
    }

    .form-control,
    .form-select {
        border-radius: var(--radius-md);
        border: 2px solid #e2e8f0;
        padding: 15px 20px;
        font-size: 15px;
        transition: var(--transition);
        background: var(--light-bg);
        color: var(--text-dark);
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(0, 34, 124, 0.1);
        background: var(--white);
    }

    .form-label {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 8px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: var(--primary-color);
        border: none;
        padding: 15px 30px;
        border-radius: var(--radius-md);
        font-weight: 700;
        font-size: 15px;
        transition: var(--transition);
        width: 100%;
        color: var(--white);
        box-shadow: 0 4px 10px rgba(0, 34, 124, 0.2);
    }

    .btn-primary:hover {
        background: var(--secondary-color);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0, 34, 124, 0.3);
        color: var(--white);
    }

    .btn-secondary {
        background: #f1f5f9;
        color: var(--text-dark);
        border: none;
        padding: 15px 30px;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 15px;
        transition: var(--transition);
        text-decoration: none;
        display: inline-block;
        text-align: center;
        width: 100%;
        margin-top: 10px;
    }

    .btn-secondary:hover {
        background: #e2e8f0;
        transform: translateY(-2px);
        color: var(--text-dark);
    }

    .alert {
        border-radius: var(--radius-md);
        border: none;
        padding: 15px 20px;
        margin-bottom: 25px;
        font-weight: 500;
    }

    .alert-success {
        background: #d1fae5;
        color: #059669;
    }

    .alert-danger {
        background: #fee2e2;
        color: #dc2626;
    }

    .input-group {
        position: relative;
    }

    .input-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--primary-color);
        z-index: 10;
    }

    .form-control.with-icon {
        padding-left: 45px;
    }

    .password-toggle {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #666;
        z-index: 10;
    }

    /* Loading Screen */
    .loading-screen {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .loading-screen.show {
        opacity: 1;
        visibility: visible;
    }

    .spinner {
        width: 70px;
        text-align: center;
    }

    .spinner>div {
        width: 18px;
        height: 18px;
        background-color: #f97316;
        border-radius: 100%;
        display: inline-block;
        animation: sk-bouncedelay 1.4s infinite ease-in-out both;
        margin: 0 3px;
    }

    .spinner .bounce1 {
        animation-delay: -0.32s;
    }

    .spinner .bounce2 {
        animation-delay: -0.16s;
    }

    @keyframes sk-bouncedelay {

        0%,
        80%,
        100% {
            transform: scale(0);
        }

        40% {
            transform: scale(1.0);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 576px) {
        .form-container {
            margin: 10px;
            padding: 30px 20px;
        }

        .form-header h1 {
            font-size: 24px;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1 class="page-title"><i class="fas fa-user-plus"></i> Tambah User</h1>
        <p class="page-subtitle">Tambah pengguna baru ke sistem</p>
    </div>
</div>

<div class="container-fluid">
    <!-- Navigation Buttons -->
    <div class="nav-buttons"
        style="display: flex; gap: 10px; margin-bottom: 30px; flex-wrap: wrap; justify-content: center;">
        <a href="index.php" class="nav-btn"
            style="background: rgba(255, 255, 255, 0.9); color: var(--primary-color); padding: 12px 20px; border-radius: 25px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="data_barang.php" class="nav-btn"
            style="background: rgba(255, 255, 255, 0.9); color: var(--primary-color); padding: 12px 20px; border-radius: 25px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
            <i class="fas fa-box"></i> Data Barang
        </a>
        <a href="kategori.php" class="nav-btn"
            style="background: rgba(255, 255, 255, 0.9); color: var(--primary-color); padding: 12px 20px; border-radius: 25px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
            <i class="fas fa-tags"></i> Kategori
        </a>
        <a href="pesanan.php" class="nav-btn"
            style="background: rgba(255, 255, 255, 0.9); color: var(--primary-color); padding: 12px 20px; border-radius: 25px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
            <i class="fas fa-shopping-cart"></i> Pesanan
        </a>
        <a href="user.php" class="nav-btn"
            style="background: var(--primary-color); color: white; padding: 12px 20px; border-radius: 25px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
            <i class="fas fa-users"></i> User
        </a>
        <a href="history_admin.php" class="nav-btn"
            style="background: rgba(255, 255, 255, 0.9); color: var(--primary-color); padding: 12px 20px; border-radius: 25px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
            <i class="fas fa-history"></i> History
        </a>
    </div>

    <div class="form-container" style="margin: 0 auto;">
        <div class="form-header">
            <h1><i class="fas fa-user-plus"></i> Tambah User Baru</h1>
            <p>Isi form di bawah untuk menambah pengguna baru</p>
        </div>

        <!-- Alert Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="username" class="form-label">
                    <i class="fas fa-user"></i> Username
                </label>
                <div class="input-group">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" class="form-control with-icon" name="username" id="username"
                        placeholder="Masukkan username" required>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">
                    <i class="fas fa-lock"></i> Password
                </label>
                <div class="input-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" class="form-control with-icon" name="password" id="password"
                        placeholder="Masukkan password" required minlength="6">
                    <i class="fas fa-eye password-toggle" onclick="togglePassword()"></i>
                </div>
            </div>

            <div class="mb-4">
                <label for="level" class="form-label">
                    <i class="fas fa-shield-alt"></i> Level
                </label>
                <div class="input-group">
                    <i class="fas fa-shield-alt input-icon"></i>
                    <select class="form-select with-icon" name="level" id="level" required>
                        <option value="">Pilih Level</option>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>

            <button type="submit" name="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Tambah User
            </button>

            <a href="user.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </form>
        </form>
    </div>
</div>

<script>
    function togglePassword() {
        const passwordField = document.getElementById('password');
        const toggleIcon = document.querySelector('.password-toggle');

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }

    // Form validation
    document.querySelector('form').addEventListener('submit', function (e) {
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        const level = document.getElementById('level').value;

        if (!username || !password || !level) {
            e.preventDefault();
            alert('Semua field harus diisi!');
            return false;
        }

        if (password.length < 6) {
            e.preventDefault();
            alert('Password minimal 6 karakter!');
            return false;
        }

        if (username.length < 3) {
            e.preventDefault();
            alert('Username minimal 3 karakter!');
            return false;
        }
    });

    // Auto redirect after success
    <?php if (isset($success_message)): ?>
        setTimeout(function () {
            window.location.href = 'user.php';
        }, 2000);
    <?php endif; ?>

</script>

<?php include 'include/admin_footer.php'; ?>