<?php
// Ensure admin access is required
if (!defined('ADMIN_ACCESS')) {
    session_start();
    if (!isset($_SESSION['username']) || $_SESSION['level'] !== 'admin') {
        header("Location: login.php");
        exit();
    }
}

$current_user = $_SESSION['username'] ?? 'Admin';
$page_title = $page_title ?? 'Admin Panel';
?>
<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#00227c">
    <title><?php echo htmlspecialchars($page_title); ?> - Snack In Admin</title>
    
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #00227c;
            --secondary-color: #001a5e;
            --accent-color: #f48c06;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-blue: #00227c;
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --orange-bg: #f69e22;
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
            background: var(--orange-bg);
            min-height: 100vh;
            color: #333;
            line-height: 1.6;
        }

        /* Simple Loading Screen */
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-screen.show {
            display: flex;
        }

        .loading-content {
            text-align: center;
            color: white;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        .loading-text {
            font-size: 16px;
            font-weight: 500;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Main Header */
        .main-header {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--box-shadow);
        }

        .main-header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 700;
            text-decoration: none;
            color: var(--white);
        }

        .logo:hover {
            color: var(--white);
        }

        .logo img {
            height: 40px;
            width: auto;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        /* Profile Dropdown */
        .profile-dropdown {
            position: relative;
            display: inline-block;
        }

        .profile-trigger {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--white);
            font-weight: 500;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 25px;
            transition: var(--transition);
            text-decoration: none;
        }

        .profile-trigger:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--white);
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #f69e22;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            text-transform: uppercase;
        }

        .dropdown-menu-custom {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
            margin-top: 8px;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .dropdown-menu-custom.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item-custom {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #333;
            text-decoration: none;
            transition: var(--transition);
            border-bottom: 1px solid #f0f0f0;
        }

        .dropdown-item-custom:last-child {
            border-bottom: none;
        }

        .dropdown-item-custom:hover {
            background-color: #f8f9fa;
            color: #333;
        }

        .dropdown-item-custom i {
            width: 16px;
            text-align: center;
            font-size: 14px;
        }

        .dropdown-item-custom.website-item i {
            color: #6b7280;
        }

        .dropdown-item-custom.logout-item i {
            color: #ef4444;
        }

        .dropdown-item-custom.logout-item:hover {
            background-color: #fef2f2;
            color: #ef4444;
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
            text-align: center;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .page-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Navigation Buttons */
        .nav-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.9);
            color: var(--primary-color);
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .nav-btn:hover {
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            color: var(--primary-color);
        }

        .nav-btn.active {
            background: var(--primary-color);
            color: white;
        }

        /* Content Container */
        .content-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin: 20px;
            backdrop-filter: blur(10px);
        }

        /* Alert Styles */
        .alert {
            border-radius: 15px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
            color: white;
        }

        .alert-danger {
            background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%);
            color: white;
        }

        /* Enhanced Table */
        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-top: 20px;
        }

        .enhanced-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            background: white;
        }

        .enhanced-table thead th {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 14px;
            border: none;
        }

        .enhanced-table tbody td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }

        .enhanced-table tbody tr {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.5s ease forwards;
            transition: var(--transition);
        }

        .enhanced-table tbody tr:nth-child(1) { animation-delay: 0.1s; }
        .enhanced-table tbody tr:nth-child(2) { animation-delay: 0.2s; }
        .enhanced-table tbody tr:nth-child(3) { animation-delay: 0.3s; }
        .enhanced-table tbody tr:nth-child(4) { animation-delay: 0.4s; }
        .enhanced-table tbody tr:nth-child(5) { animation-delay: 0.5s; }
        .enhanced-table tbody tr:nth-child(n+6) { animation-delay: 0.6s; }

        .enhanced-table tbody tr:hover {
            background-color: #f8f9ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* Simple Table Animation */
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

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #000f3d 100%);
            transform: translateY(-2px);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-2px);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%);
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-2px);
            color: white;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        /* Badge Styles */
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .bg-primary { background-color: var(--primary-color); color: white; }
        .bg-success { background-color: var(--success-color); color: white; }
        .bg-warning { background-color: var(--warning-color); color: black; }
        .bg-danger { background-color: var(--danger-color); color: white; }
        .bg-info { background-color: #17a2b8; color: white; }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-header .container {
                padding: 0 15px;
            }

            .logo {
                font-size: 20px;
            }

            .logo img {
                height: 32px;
            }

            .content-container {
                margin: 10px;
                padding: 20px;
            }

            .nav-buttons {
                justify-content: center;
            }

            .nav-btn {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <!-- Simple Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="loading-content">
            <div class="spinner"></div>
            <div class="loading-text">Loading...</div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header">
        <div class="container">
            <a href="index.php" class="logo" onclick="showLoading()">
                <<img src="assets/img/Logo Bisnis Bengkel Otomotif (3).png" alt="Snack In Logo">
                <span>SNACK IN ADMIN</span>
            </a>
            <div class="header-actions">
                <div class="profile-dropdown">
                    <div class="profile-trigger" id="profileTrigger">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($current_user, 0, 1)); ?>
                        </div>
                        <span><?php echo htmlspecialchars($current_user); ?></span>
                        <i class="fas fa-chevron-down" style="font-size: 12px; margin-left: 5px;"></i>
                    </div>
                    <div class="dropdown-menu-custom" id="profileDropdown">
                        <a href="user_ui.php" class="dropdown-item-custom website-item" onclick="showLoading()">
                            <i class="fas fa-globe"></i>
                            <span>Go to User Website</span>
                        </a>
                        <a href="logout.php" class="dropdown-item-custom logout-item" onclick="showLoading()">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Exit</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <script>
        // Simple Loading Functions
        function showLoading() {
            const loadingScreen = document.getElementById('loadingScreen');
            if (loadingScreen) {
                loadingScreen.classList.add('show');
            }
        }
        
        function hideLoading() {
            const loadingScreen = document.getElementById('loadingScreen');
            if (loadingScreen) {
                loadingScreen.classList.remove('show');
            }
        }

        // Profile dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const profileTrigger = document.getElementById('profileTrigger');
            const profileDropdown = document.getElementById('profileDropdown');

            if (profileTrigger && profileDropdown) {
                profileTrigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    profileDropdown.classList.toggle('show');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!profileTrigger.contains(e.target) && !profileDropdown.contains(e.target)) {
                        profileDropdown.classList.remove('show');
                    }
                });
            }

            // Hide loading screen when page is loaded
            setTimeout(hideLoading, 500);

            // Show loading on page navigation
            document.querySelectorAll('a:not([href^="#"]):not([href^="javascript:"]):not([target="_blank"])').forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!this.closest('.dropdown-menu-custom') && this.href !== window.location.href) {
                        showLoading();
                    }
                });
            });

            // Show loading on form submission
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (this.checkValidity()) {
                        showLoading();
                    }
                });
            });
        });

        // Hide loading on window load
        window.addEventListener('load', function() {
            setTimeout(hideLoading, 300);
        });

        // Hide loading when page becomes visible
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                setTimeout(hideLoading, 200);
            }
        });
    </script>
</body>
</html>
