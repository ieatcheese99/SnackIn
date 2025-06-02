<?php
define('ADMIN_ACCESS', true);

// Tidak menggunakan config/functions.php
require_once 'config/database.php';

session_start();

// Fungsi sanitasi input
function sanitize_input($data) {
    global $db;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($db, $data);
}

// Fungsi untuk memeriksa akses admin
function requireAdmin() {
    if (!isset($_SESSION['username']) || $_SESSION['level'] !== 'admin') {
        header("Location: login.php");
        exit();
    }
}

// Fungsi untuk mencatat aktivitas admin
function logAdminAction($action, $details) {
    global $db;
    $username = $_SESSION['username'];
    $query = "INSERT INTO admin_logs (username, action, details, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = mysqli_prepare($db, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'sss', $username, $action, $details);
        mysqli_stmt_execute($stmt);
    }
}

// Cek akses admin
requireAdmin();

// Get user ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query = "SELECT * FROM user WHERE id = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    header("Location: user.php");
    exit();
}

// Handle form submission
if (isset($_POST['submit'])) {
    $username = sanitize_input($_POST['username']);
    $level = sanitize_input($_POST['level']);
    
    // Check if username already exists (excluding current user)
    $check_query = "SELECT COUNT(*) as count FROM user WHERE username = ? AND id != ?";
    $check_stmt = mysqli_prepare($db, $check_query);
    mysqli_stmt_bind_param($check_stmt, 'si', $username, $id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $check_data = mysqli_fetch_assoc($check_result);
    
    if ($check_data['count'] > 0) {
        $error_message = "Username sudah digunakan!";
    } else {
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
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "User berhasil diperbarui!";
            logAdminAction("Edit User", "ID: $id, Username: $username, Level: $level");
            // Update user data for display
            $user['username'] = $username;
            $user['level'] = $level;
        } else {
            $error_message = "Gagal mengupdate user!";
        }
    }
}

$page_title = "Edit User";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Snack In Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #00227c;
            --secondary-color: #1e40af;
            --accent-color: #f48c06;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-blue: #00227c;
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --border-radius: 12px;
            --box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f97316, #ea580c);
            min-height: 100vh;
            color: #333;
            line-height: 1.6;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            margin: 20px;
            animation: fadeIn 0.5s ease;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .form-subtitle {
            color: #666;
            margin: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-control, .form-select {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            padding: 15px 20px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 34, 124, 0.1);
            background: white;
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
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

        .btn {
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 16px;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 34, 124, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.3);
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 25px;
            font-weight: 600;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
        }

        .user-avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 24px;
            color: white;
            margin: 0 auto 16px;
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

        .spinner > div {
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
            0%, 80%, 100% { 
                transform: scale(0);
            } 40% { 
                transform: scale(1.0);
            }
        }

        /* Page Transition Animation */
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
            
            .form-title {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="spinner">
            <div class="bounce1"></div>
            <div class="bounce2"></div>
            <div class="bounce3"></div>
        </div>
    </div>

    <div class="form-container">
        <div class="form-header">
            <div class="user-avatar">
                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
            </div>
            <h1 class="form-title">Edit User</h1>
            <p class="form-subtitle">Edit informasi pengguna</p>
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

        <form method="POST" id="editUserForm">
            <div class="form-group">
                <label for="username" class="form-label">
                    <i class="fas fa-user"></i> Username
                </label>
                <div class="input-group">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" class="form-control with-icon" name="username" id="username" 
                           placeholder="Username" value="<?php echo htmlspecialchars($user['username']); ?>" required minlength="3">
                </div>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">
                    <i class="fas fa-lock"></i> Password
                </label>
                <div class="input-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" class="form-control with-icon" name="password" id="password" 
                           placeholder="Kosongkan jika tidak ingin diubah" minlength="6">
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('password')"></i>
                </div>
                <div style="margin-top: 8px; font-size: 12px; color: #666; display: flex; align-items: center; gap: 4px;">
                    <i class="fas fa-info-circle"></i> Kosongkan jika tidak ingin mengubah password
                </div>
            </div>
            
            <div class="form-group">
                <label for="level" class="form-label">
                    <i class="fas fa-shield-alt"></i> Level
                </label>
                <div class="input-group">
                    <i class="fas fa-shield-alt input-icon"></i>
                    <select class="form-control with-icon" name="level" id="level" required>
                        <option value="user" <?php echo ($user['level'] == 'user') ? 'selected' : ''; ?>>User</option>
                        <option value="admin" <?php echo ($user['level'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
            </div>
            
            <div style="margin-top: 32px;">
                <button type="submit" name="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 12px;">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                
                <a href="user.php" class="btn btn-secondary" style="width: 100%;">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show loading screen
        function showLoading() {
            document.getElementById('loadingScreen').classList.add('show');
        }
        
        // Hide loading screen
        function hideLoading() {
            document.getElementById('loadingScreen').classList.remove('show');
        }

        // Password toggle
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling;
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Form validation
        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const level = document.getElementById('level').value;
            
            if (!username || !level) {
                e.preventDefault();
                alert('Username dan level harus diisi!');
                return false;
            }
            
            if (password && password.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter!');
                return false;
            }
            
            if (username.length < 3) {
                e.preventDefault();
                alert('Username minimal 3 karakter!');
                return false;
            }
            
            // Show loading when form is submitted
            showLoading();
        });

        // Auto redirect after success
        <?php if (isset($success_message)): ?>
        setTimeout(function() {
            showLoading();
            window.location.href = 'user.php';
        }, 2000);
        <?php endif; ?>

        // Page transition animation
        window.addEventListener('load', function() {
            hideLoading();
        });

        // Show loading on page navigation
        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function() {
                showLoading();
            });
        });
    </script>
</body>
</html>
