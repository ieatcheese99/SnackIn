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
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/img/Logo Bisnis Bengkel Otomotif (3).png">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
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

        /* Animated background particles */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .particle:nth-child(1) {
            width: 10px;
            height: 10px;
            left: 10%;
            animation-delay: 0s;
        }

        .particle:nth-child(2) {
            width: 15px;
            height: 15px;
            left: 20%;
            animation-delay: 1s;
        }

        .particle:nth-child(3) {
            width: 8px;
            height: 8px;
            left: 30%;
            animation-delay: 2s;
        }

        .particle:nth-child(4) {
            width: 12px;
            height: 12px;
            left: 40%;
            animation-delay: 3s;
        }

        .particle:nth-child(5) {
            width: 6px;
            height: 6px;
            left: 50%;
            animation-delay: 4s;
        }

        .particle:nth-child(6) {
            width: 14px;
            height: 14px;
            left: 60%;
            animation-delay: 5s;
        }

        .particle:nth-child(7) {
            width: 9px;
            height: 9px;
            left: 70%;
            animation-delay: 0.5s;
        }

        .particle:nth-child(8) {
            width: 11px;
            height: 11px;
            left: 80%;
            animation-delay: 1.5s;
        }

        .particle:nth-child(9) {
            width: 7px;
            height: 7px;
            left: 90%;
            animation-delay: 2.5s;
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
            animation: bounceIn 1s ease-out;
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
            animation: slideInLeft 1s ease-out;
        }

        .welcome-content p {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
            max-width: 400px;
            margin: 0 auto;
            animation: slideInLeft 1s ease-out 0.2s both;
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
            animation: float 3s ease-in-out infinite;
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
            animation: pulse 2s ease-in-out infinite;
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
            animation: slideInRight 1s ease-out;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-header .logo-section {
            margin-bottom: 1.5rem;
            animation: bounceIn 1s ease-out 0.3s both;
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
            animation: fadeInDown 1s ease-out 0.4s both;
        }

        .form-header p {
            color: #64748b;
            font-size: 0.95rem;
            animation: fadeInDown 1s ease-out 0.5s both;
        }

        .form-group {
            margin-bottom: 1.5rem;
            animation: fadeInUp 0.6s ease-out calc(0.6s + var(--delay)) both;
        }

        .form-group:nth-child(1) {
            --delay: 0.1s;
        }

        .form-group:nth-child(2) {
            --delay: 0.2s;
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
            transition: all 0.3s ease;
            background: #f9fafb;
        }

        .form-control:focus {
            outline: none;
            border-color: #f69e22;
            background: white;
            box-shadow: 0 0 0 3px rgba(246, 158, 34, 0.1);
            transform: translateY(-2px);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            animation: fadeInUp 1s ease-out 0.9s both;
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
            transition: all 0.3s ease;
        }

        .form-check input[type="checkbox"]:hover {
            transform: scale(1.1);
        }

        .form-check label {
            font-size: 0.875rem;
            color: #64748b;
            margin: 0;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .form-check label:hover {
            color: #f69e22;
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
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 34, 124, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            animation: fadeInUp 1s ease-out 1s both;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 34, 124, 0.4);
        }

        .btn-login:active {
            transform: translateY(-1px);
        }

        .register-link {
            text-align: center;
            font-size: 0.875rem;
            color: #64748b;
            animation: fadeInUp 1s ease-out 1.1s both;
        }

        .register-link a {
            color: #00227c;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .register-link a:hover {
            text-decoration: underline;
            color: #f69e22;
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
            animation: slideInDown 0.5s ease-out;
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
            0% {
                transform: translate(-50%, -50%) rotate(0deg);
            }

            100% {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        /* Animations */
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.3);
            }

            50% {
                opacity: 1;
                transform: scale(1.05);
            }

            70% {
                transform: scale(0.9);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translateX(-50%) translateY(0px);
            }

            50% {
                transform: translateX(-50%) translateY(-15px);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        /* Hide fallback when image loads successfully */
        .snack-image:not([src=""])~.snack-fallback {
            display: none;
        }

        /* Input focus effects */
        .form-control:focus {
            animation: inputFocus 0.3s ease-out;
        }

        @keyframes inputFocus {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.02);
            }

            100% {
                transform: scale(1);
            }
        }

        /* Button click effect */
        .btn-login:active {
            animation: buttonClick 0.2s ease-out;
        }

        @keyframes buttonClick {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(0.98);
            }

            100% {
                transform: scale(1);
            }
        }
    </style>
</head>

<body>
    <!-- Animated background particles -->
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <div class="container">
        <!-- Left side with branding -->
        <div class="left-side">
            <div class="brand-section">
                <div class="logo">
                    <img src="images\AHLINYA CEMILAN.png" alt="Snack In Logo" class="logo-image">
                </div>
                <div class="welcome-content">
                    <h1>Selamat Datang Kembali!</h1>
                    <p>Masuk ke akun Anda untuk menikmati berbagai produk makanan ringan berkualitas tinggi dari Snack
                        In.</p>
                </div>
            </div>

            <!-- Snack Illustration dengan Image -->
            <div class="snack-illustration">
                <!-- Ganti src dengan path gambar snack Anda -->
                <img src="/placeholder.svg?height=120&width=120" alt="Snack Illustration" class="snack-image"
                    onerror="this.style.display='none'">

                <!-- Fallback illustration jika gambar tidak ada -->
                <div class="snack-fallback">🍿</div>
            </div>
        </div>

        <!-- Right side with login form -->
        <div class="right-side">
            <div class="login-form">
                <div class="form-header">
                    <div class="logo-section">
                        <img src="images\AHLINYA CEMILAN.png" alt="Snack In Logo">
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
                        <input type="text" class="form-control" id="username" name="username"
                            placeholder="Masukkan username" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Masukkan password" required>
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
        document.getElementById('loginForm').addEventListener('submit', function () {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });

        // Add focus animations
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function () {
                this.parentElement.style.transform = 'translateY(-2px)';
                this.parentElement.style.transition = 'all 0.3s ease';
            });

            input.addEventListener('blur', function () {
                this.parentElement.style.transform = 'translateY(0)';
            });

            // Typing animation effect
            input.addEventListener('input', function () {
                this.style.animation = 'inputFocus 0.3s ease-out';
                setTimeout(() => {
                    this.style.animation = '';
                }, 300);
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

        // Floating particles animation
        function createParticle() {
            const particles = document.querySelectorAll('.particle');
            particles.forEach(particle => {
                const randomY = Math.random() * window.innerHeight;
                particle.style.top = randomY + 'px';
            });
        }

        // Initialize particles
        createParticle();

        // Recreate particles on window resize
        window.addEventListener('resize', createParticle);

        // Add ripple effect to button
        document.getElementById('submitBtn').addEventListener('click', function (e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.style.position = 'absolute';
            ripple.style.borderRadius = '50%';
            ripple.style.background = 'rgba(255, 255, 255, 0.3)';
            ripple.style.transform = 'scale(0)';
            ripple.style.animation = 'ripple 0.6s linear';
            ripple.style.pointerEvents = 'none';

            this.appendChild(ripple);

            setTimeout(() => {
                ripple.remove();
            }, 600);
        });

        // Add ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        // Form validation with animation
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

            if (!username || !password) {
                e.preventDefault();

                // Shake animation for empty fields
                if (!username) {
                    document.getElementById('username').style.animation = 'shake 0.5s ease-in-out';
                }
                if (!password) {
                    document.getElementById('password').style.animation = 'shake 0.5s ease-in-out';
                }

                setTimeout(() => {
                    document.getElementById('username').style.animation = '';
                    document.getElementById('password').style.animation = '';
                }, 500);

                return false;
            }
        });

        // Add shake animation
        const shakeStyle = document.createElement('style');
        shakeStyle.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                20%, 40%, 60%, 80% { transform: translateX(5px); }
            }
        `;
        document.head.appendChild(shakeStyle);
    </script>
</body>

</html>