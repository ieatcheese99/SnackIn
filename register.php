<?php
session_start();
require "config/database.php";

// Redirect jika sudah login
if (isset($_SESSION['username'])) {
    if ($_SESSION['level'] == "admin") {
        header("Location: index.php");
    } else {
        header("Location: user_ui.php");
    }
    exit();
}

// Fungsi untuk membersihkan input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Proses registrasi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    // Validasi input
    if (empty($username)) {
        $errors[] = "Username harus diisi!";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username minimal 3 karakter!";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username hanya boleh mengandung huruf, angka, dan underscore!";
    }
    
    if (empty($password)) {
        $errors[] = "Password harus diisi!";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter!";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Konfirmasi password tidak cocok!";
    }
    
    // Cek apakah username sudah ada
    if (empty($errors)) {
        $check_username = mysqli_query($db, "SELECT username FROM user WHERE username='$username'");
        if (mysqli_num_rows($check_username) > 0) {
            $errors[] = "Username sudah digunakan! Silakan pilih username lain.";
        }
    }
    
    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO user (username, password, level) VALUES (?, ?, 'user')";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $username, $hashed_password);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Registrasi berhasil! Silakan login dengan akun Anda.";
            // Auto redirect setelah 3 detik
            header("refresh:3;url=login.php");
        } else {
            $errors[] = "Terjadi kesalahan saat mendaftar. Silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Snack In</title>
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

        .particle:nth-child(1) { width: 10px; height: 10px; left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { width: 15px; height: 15px; left: 20%; animation-delay: 1s; }
        .particle:nth-child(3) { width: 8px; height: 8px; left: 30%; animation-delay: 2s; }
        .particle:nth-child(4) { width: 12px; height: 12px; left: 40%; animation-delay: 3s; }
        .particle:nth-child(5) { width: 6px; height: 6px; left: 50%; animation-delay: 4s; }
        .particle:nth-child(6) { width: 14px; height: 14px; left: 60%; animation-delay: 5s; }
        .particle:nth-child(7) { width: 9px; height: 9px; left: 70%; animation-delay: 0.5s; }
        .particle:nth-child(8) { width: 11px; height: 11px; left: 80%; animation-delay: 1.5s; }
        .particle:nth-child(9) { width: 7px; height: 7px; left: 90%; animation-delay: 2.5s; }

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

        .features-list {
            margin-top: 2rem;
            text-align: left;
            animation: slideInLeft 1s ease-out 0.4s both;
        }

        .features-list ul {
            list-style: none;
            padding: 0;
        }

        .features-list li {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 0.95rem;
            opacity: 0.9;
            animation: slideInLeft 0.6s ease-out calc(0.6s + var(--delay)) both;
        }

        .features-list li:nth-child(1) { --delay: 0.1s; }
        .features-list li:nth-child(2) { --delay: 0.2s; }
        .features-list li:nth-child(3) { --delay: 0.3s; }
        .features-list li:nth-child(4) { --delay: 0.4s; }

        .features-list li i {
            color: #f69e22;
            margin-right: 0.75rem;
            font-size: 1.1rem;
            animation: pulse 2s ease-in-out infinite;
        }

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
            overflow-y: auto;
        }

        .register-form {
            width: 100%;
            max-width: 420px;
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideInRight 1s ease-out;
            margin: 2rem 0;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
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
            animation: fadeInDown 1s ease-out 0.3s both;
        }

        .form-header p {
            color: #64748b;
            font-size: 0.95rem;
            animation: fadeInDown 1s ease-out 0.4s both;
        }

        .form-group {
            margin-bottom: 1.5rem;
            animation: fadeInUp 0.6s ease-out calc(0.5s + var(--delay)) both;
        }

        .form-group:nth-child(1) { --delay: 0.1s; }
        .form-group:nth-child(2) { --delay: 0.2s; }
        .form-group:nth-child(3) { --delay: 0.3s; }

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

        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.75rem;
            transition: all 0.3s ease;
        }

        .strength-weak { color: #dc2626; }
        .strength-medium { color: #f59e0b; }
        .strength-strong { color: #10b981; }

        .btn-register {
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
            animation: fadeInUp 1s ease-out 0.8s both;
        }

        .btn-register:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 34, 124, 0.4);
        }

        .btn-register:active {
            transform: translateY(-1px);
        }

        .login-link {
            text-align: center;
            font-size: 0.875rem;
            color: #64748b;
            animation: fadeInUp 1s ease-out 0.9s both;
        }

        .login-link a {
            color: #00227c;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            text-decoration: underline;
            color: #f69e22;
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            animation: slideInDown 0.5s ease-out;
        }

        .alert-success {
            background: linear-gradient(45deg, #f0fdf4, #dcfce7);
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-danger {
            background: linear-gradient(45deg, #fef2f2, #fee2e2);
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .alert ul {
            margin: 0;
            padding-left: 1rem;
        }

        .alert li {
            margin-bottom: 0.25rem;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .form-control.with-icon {
            padding-left: 2.5rem;
        }

        .form-control:focus + .input-icon {
            color: #f69e22;
        }

        .input-group {
            position: relative;
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
            
            .register-form {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
            }
        }

        @media (max-width: 640px) {
            .right-side {
                padding: 1rem;
            }
            
            .register-form {
                padding: 2rem 1.5rem;
                max-width: 100%;
            }
            
            .welcome-content h1 {
                font-size: 2rem;
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
            0%, 100% { transform: translateX(-50%) translateY(0px); }
            50% { transform: translateX(-50%) translateY(-15px); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .btn-register.loading {
            position: relative;
            color: transparent;
        }

        .btn-register.loading::after {
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

        /* Success animation */
        .success-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: #10b981;
            border-radius: 50%;
            color: white;
            font-size: 1.2rem;
            animation: successPop 0.6s ease-out;
            flex-shrink: 0;
        }

        @keyframes successPop {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.2);
                opacity: 1;
            }
            100% {
                transform: scale(1);
                opacity: 1;
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
                    <h1>Bergabung dengan Snack In!</h1>
                    <p>Daftarkan diri Anda dan nikmati berbagai produk makanan ringan berkualitas tinggi dengan pengalaman berbelanja yang menyenangkan.</p>
                </div>
                
                <div class="features-list">
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Produk snack berkualitas tinggi</li>
                        <li><i class="fas fa-truck"></i> Pengiriman cepat dan aman</li>
                        <li><i class="fas fa-star"></i> Harga terjangkau dan kompetitif</li>
                        <li><i class="fas fa-headset"></i> Customer service 24/7</li>
                    </ul>
                </div>
            </div>
            
            <div class="snack-illustration">
                <img src="/placeholder.svg?height=120&width=120" alt="Snack Illustration" class="snack-image" onerror="this.style.display='none'">
                <div class="snack-fallback">🍿</div>
            </div>
        </div>

        <!-- Right side with register form -->
        <div class="right-side">
            <div class="register-form">
                <div class="form-header">
                    <h2><i class="fas fa-user-plus"></i> Daftar Akun</h2>
                    <p>Buat akun baru untuk mulai berbelanja</p>
                </div>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <div class="success-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <?php echo $success_message; ?>
                            <br><small>Anda akan diarahkan ke halaman login...</small>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" id="registerForm">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-group">
                            <input type="text" class="form-control with-icon" id="username" name="username" 
                                   placeholder="Masukkan username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required minlength="3">
                            <i class="fas fa-user input-icon"></i>
                        </div>
                        <small style="color: #666; font-size: 0.75rem; margin-top: 0.25rem; display: block;">Username hanya boleh mengandung huruf, angka, dan underscore</small>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control with-icon" id="password" name="password" 
                                   placeholder="Masukkan password" required minlength="6">
                            <i class="fas fa-lock input-icon"></i>
                        </div>
                        <div class="password-strength" id="passwordStrength"></div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control with-icon" id="confirm_password" name="confirm_password" 
                                   placeholder="Ulangi password" required minlength="6">
                            <i class="fas fa-lock input-icon"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn-register" id="submitBtn">
                        <i class="fas fa-user-plus"></i> Daftar Sekarang
                    </button>

                    <div class="login-link">
                        <p>Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
                        <p><a href="user_ui.php">Kembali ke Beranda</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Form validation and animations
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Validasi username format
            const usernameRegex = /^[a-zA-Z0-9_]+$/;
            if (!usernameRegex.test(username)) {
                e.preventDefault();
                alert('Username hanya boleh mengandung huruf, angka, dan underscore!');
                return false;
            }
            
            // Validasi password match
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Konfirmasi password tidak cocok!');
                return false;
            }
            
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });

        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            
            let strength = 0;
            let message = '';
            
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            switch (strength) {
                case 0:
                case 1:
                    message = '<span class="strength-weak"><i class="fas fa-times-circle"></i> Lemah</span>';
                    break;
                case 2:
                case 3:
                    message = '<span class="strength-medium"><i class="fas fa-exclamation-circle"></i> Sedang</span>';
                    break;
                case 4:
                case 5:
                    message = '<span class="strength-strong"><i class="fas fa-check-circle"></i> Kuat</span>';
                    break;
            }
            
            strengthDiv.innerHTML = message;
        });

        // Confirm password validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Password tidak cocok');
                this.style.borderColor = '#dc2626';
            } else {
                this.setCustomValidity('');
                this.style.borderColor = '#e5e7eb';
            }
        });

        // Form field animations
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach((input, index) => {
            input.addEventListener('focus', function() {
                this.parentElement.parentElement.style.transform = 'translateY(-2px)';
                this.parentElement.parentElement.style.transition = 'all 0.3s ease';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.parentElement.style.transform = 'translateY(0)';
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

        // Username validation
        document.getElementById('username').addEventListener('input', function() {
            const username = this.value;
            const usernameRegex = /^[a-zA-Z0-9_]+$/;
            
            if (username && !usernameRegex.test(username)) {
                this.style.borderColor = '#dc2626';
                this.setCustomValidity('Username hanya boleh mengandung huruf, angka, dan underscore');
            } else {
                this.style.borderColor = '#e5e7eb';
                this.setCustomValidity('');
            }
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
    </script>
</body>
</html>
