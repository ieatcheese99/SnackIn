<?php
session_start();
require "config/database.php";

if (isset($_SESSION['username'])) {
    // Redirect jika sudah login
    if ($_SESSION['level'] == "admin") {
        header("Location: index.php");
    } else {
        header("Location: user_ui.php");
    }
    exit();
}

// Cek jika ada cookie remember me
if (isset($_COOKIE['username']) && isset($_COOKIE['password'])) {
    $username = $_COOKIE['username'];
    $password = $_COOKIE['password'];

    $query = mysqli_query($db, "SELECT * FROM user WHERE username='$username'");
    $data = mysqli_fetch_assoc($query);

    if ($data && password_verify($password, $data['password'])) {
        $_SESSION['username'] = $data['username'];
        $_SESSION['level'] = $data['level'];

        if ($data['level'] == "admin") {
            header("Location: index.php");
        } else {
            header("Location: user_ui.php");
        }
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    $query = mysqli_query($db, "SELECT * FROM user WHERE username='$username'");
    $data = mysqli_fetch_assoc($query);

    if ($data && password_verify($password, $data['password'])) {
        $_SESSION['username'] = $data['username'];
        $_SESSION['level'] = $data['level'];

        if ($remember) {
            setcookie("username", $username, time() + (86400 * 30), "/");
            setcookie("password", $password, time() + (86400 * 30), "/");
        }

        if ($data['level'] == "admin") {
            header("Location: index.php");
        } else {
            header("Location: user_ui.php");
        }
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Snack In</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #00227c 0%, #1e40af 50%, #f69e22 100%);
            position: relative;
            overflow-x: hidden;
        }

        /* Background decoration */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="20" cy="80" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
        }

        .container {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            position: relative;
            z-index: 1;
        }

        .left-side {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            color: white;
            position: relative;
            min-height: 100vh;
        }

        .brand-section {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1;
            max-width: 500px;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2.5rem;
        }

        .logo-image {
            max-width: 200px;
            max-height: 80px;
            width: auto;
            height: auto;
            object-fit: contain;
            filter: brightness(1.2) contrast(1.1);
        }

        .welcome-content {
            text-align: center;
        }

        .welcome-content h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .welcome-content p {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
            max-width: 400px;
            margin: 0 auto;
        }

        /* Snack Illustration dengan Image */
        .snack-illustration {
            position: absolute;
            bottom: 3rem;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .snack-image {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.3));
        }

        /* Fallback untuk ilustrasi jika gambar tidak ada */
        .snack-fallback {
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .right-side {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            position: relative;
        }

        .login-form {
            width: 100%;
            max-width: 420px;
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-header .logo-section {
            margin-bottom: 1.5rem;
        }

        .form-header .logo-section img {
            height: 60px;
            margin-bottom: 10px;
        }

        .form-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #00227c;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .form-header p {
            color: #64748b;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.2s ease;
            background: #f9fafb;
        }

        .form-control:focus {
            outline: none;
            border-color: #f69e22;
            background: white;
            box-shadow: 0 0 0 3px rgba(246, 158, 34, 0.1);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-check input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #f69e22;
        }

        .form-check label {
            font-size: 0.875rem;
            color: #64748b;
            margin: 0;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(45deg, #00227c, #1e40af);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 34, 124, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0, 34, 124, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .register-link {
            text-align: center;
            font-size: 0.875rem;
            color: #64748b;
        }

        .register-link a {
            color: #00227c;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .register-link p {
            margin-bottom: 0.5rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-danger {
            background: linear-gradient(45deg, #fef2f2, #fee2e2);
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .alert-warning {
            background: linear-gradient(45deg, #fffbeb, #fef3c7);
            color: #d97706;
            border: 1px solid #fed7aa;
        }

        @media (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
            }
            
            .left-side {
                display: none;
            }
            
            .right-side {
                background: linear-gradient(135deg, #00227c 0%, #1e40af 50%, #f69e22 100%);
            }
            
            .login-form {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
            }
        }

        @media (max-width: 640px) {
            .right-side {
                padding: 1rem;
            }
            
            .login-form {
                padding: 2rem 1.5rem;
                max-width: 100%;
            }
            
            .welcome-content h1 {
                font-size: 2rem;
            }
            
            .snack-illustration {
                width: 100px;
                height: 100px;
                bottom: 2rem;
            }
        }

        /* Loading animation */
        .btn-login.loading {
            position: relative;
            color: transparent;
        }

        .btn-login.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Floating elements animation */
        @keyframes float {
            0%, 100% { transform: translateX(-50%) translateY(0px); }
            50% { transform: translateX(-50%) translateY(-10px); }
        }

        .snack-illustration {
            animation: float 3s ease-in-out infinite;
        }

        /* Hide fallback when image loads successfully */
        .snack-image:not([src=""]) ~ .snack-fallback {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Left side with branding -->
        <div class="left-side">
            <div class="brand-section">
                <div class="logo">
                    <img src="assets/img/Logo Bisnis Bengkel Otomotif (3).png" alt="Snack In Logo" class="logo-image">
                </div>
                <div class="welcome-content">
                    <h1>Selamat Datang Kembali!</h1>
                    <p>Masuk ke akun Anda untuk menikmati berbagai produk makanan ringan berkualitas tinggi dari Snack In.</p>
                </div>
            </div>
            
            <!-- Snack Illustration dengan Image -->
            <div class="snack-illustration">
                <!-- Ganti src dengan path gambar snack Anda -->
                <img src="assets/images/snack-illustration.png" alt="Snack Illustration" class="snack-image" onerror="this.style.display='none'">
                
                <!-- Fallback illustration jika gambar tidak ada -->
                <div class="snack-fallback">🍿</div>
            </div>
        </div>

        <!-- Right side with login form -->
        <div class="right-side">
            <div class="login-form">
                <div class="form-header">
                    <div class="logo-section">
                        <img src="assets/img/Logo Bisnis Bengkel Otomotif (3).png" alt="Snack In Logo">
                    </div>
                    <h2><i class="fas fa-user-circle"></i> Login</h2>
                    <p>Masuk ke akun Anda</p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-warning">
                        <?php if ($_GET['error'] == 'login_required'): ?>
                            <i class="fas fa-lock"></i> Silakan login terlebih dahulu.
                        <?php elseif ($_GET['error'] == 'access_denied'): ?>
                            <i class="fas fa-ban"></i> Akses ditolak. Anda tidak memiliki hak akses admin.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="loginForm">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="form-options">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember">Remember Me</label>
                        </div>
                    </div>

                    <button type="submit" class="btn-login" id="submitBtn">
                        <i class="fas fa-sign-in-alt"></i> Masuk
                    </button>

                    <div class="register-link">
                        <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
                        <p><a href="user_ui.php">Kembali ke Beranda</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Add loading animation on form submit
        document.getElementById('loginForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });

        // Add focus animations
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });

        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 300);
            }, 5000);
        });
    </script>
</body>
</html>