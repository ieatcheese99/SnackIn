<?php
define('ADMIN_ACCESS', true);

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

// Cek akses admin
requireAdmin();

// Fungsi untuk menambahkan user
function create_user($post) {
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
?>

<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#000000">
    <title>Tambah User - Snack In</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        body {
            background: linear-gradient(135deg, #f97316, #ea580c);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
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

        .form-header h1 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .form-header p {
            color: #666;
            margin: 0;
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

        .form-label {
            font-weight: 600;
            color: #333;
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
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 34, 124, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
            padding: 15px 30px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            color: white;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            width: 100%;
            margin-top: 10px;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(108,117,125,0.3);
            color: white;
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
            <h1><i class="fas fa-user-plus"></i> Tambah User</h1>
            <p>Tambah pengguna baru ke sistem</p>
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
        document.querySelector('form').addEventListener('submit', function(e) {
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
