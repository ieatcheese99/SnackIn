<?php
// Ensure admin access is required
if (!defined('ADMIN_ACCESS')) {
    session_start();
    if (!isset($_SESSION['username']) || $_SESSION['level'] !== 'admin') {
        header("Location: login.php");
        exit();
    }
}

// Include security functions (which already has getCurrentUser)
require_once __DIR__ . '/../config/security.php';

$current_user = getCurrentUser();
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
            background: #f69e22;
            min-height: 100vh;
            color: #333;
            line-height: 1.6;
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
            background: var(--primary-color);
            color: var(--white);
            padding: 40px 0;
            margin-bottom: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }

        .page-header .container {
            position: relative;
            z-index: 1;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .page-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Navigation Buttons */
        .nav-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            justify-content: center;
            padding: 0 20px;
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
            box-shadow: var(--box-shadow);
            border: 2px solid transparent;
        }

        .nav-btn:hover {
            background: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            color: var(--primary-color);
        }

        .nav-btn.active {
            background: var(--primary-color);
            color: var(--white);
            border-color: var(--white);
        }

        .nav-btn.active:hover {
            background: var(--secondary-color);
            color: var(--white);
        }

        /* Content Container */
        .content-container {
            background: var(--white);
            border-radius: 20px;
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin: 20px;
            backdrop-filter: blur(10px);
            min-height: 400px;
            animation: fadeIn 0.5s ease;
        }

        /* Enhanced Table Styles */
        .enhanced-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--white);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
        }

        .enhanced-table th {
            background: var(--primary-color);
            color: var(--white);
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            border: none;
        }

        .enhanced-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
            font-size: 14px;
        }

        .enhanced-table tbody tr:hover {
            background-color: var(--light-gray);
        }

        .enhanced-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Badge Styles */
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }

        .bg-success {
            background: #10b981;
            color: white;
        }

        .bg-warning {
            background: #f59e0b;
            color: white;
        }

        .bg-danger {
            background: #ef4444;
            color: white;
        }

        .bg-info {
            background: #06b6d4;
            color: white;
        }

        .bg-primary {
            background: var(--primary-color);
            color: white;
        }

        /* Button Styles */
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            color: var(--white);
        }

        .btn-success {
            background: var(--success-color);
            color: var(--white);
        }

        .btn-success:hover {
            background: #059669;
            transform: translateY(-2px);
            color: var(--white);
        }

        .btn-warning {
            background: var(--warning-color);
            color: var(--white);
        }

        .btn-warning:hover {
            background: #d97706;
            transform: translateY(-2px);
            color: var(--white);
        }

        .btn-danger {
            background: var(--danger-color);
            color: var(--white);
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-2px);
            color: var(--white);
        }

        /* Alert Styles */
        .alert {
            border-radius: var(--border-radius);
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        /* Form Styles */
        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: var(--transition);
            width: 100%;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 34, 124, 0.1);
            outline: none;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            display: block;
        }

        /* Table Container */
        .table-container {
            overflow-x: auto;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        /* Loading Screen - Matching user_ui.php style */
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
            backdrop-filter: blur(5px);
        }

        .loading-screen.show {
            opacity: 1;
            visibility: visible;
        }

        .loading-content {
            text-align: center;
            color: white;
        }

        .spinner {
            width: 70px;
            text-align: center;
            margin: 0 auto 20px;
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

        .loading-text {
            font-size: 18px;
            font-weight: 500;
            color: white;
            margin-top: 10px;
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

        /* Responsive Design */
        @media (max-width: 992px) {
            .page-title {
                font-size: 2rem;
            }

            .nav-buttons {
                justify-content: center;
            }

            .nav-btn {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 768px) {
            .content-container {
                margin: 10px;
                padding: 20px;
            }

            .page-header {
                padding: 30px 0;
            }

            .page-title {
                font-size: 1.8rem;
            }

            .nav-buttons {
                gap: 8px;
            }

            .nav-btn {
                padding: 8px 12px;
                font-size: 0.85rem;
            }

            .enhanced-table {
                font-size: 12px;
            }

            .enhanced-table th,
            .enhanced-table td {
                padding: 8px;
            }
        }

        @media (max-width: 480px) {
            .main-header .container {
                padding: 0 15px;
            }

            .logo {
                font-size: 20px;
            }

            .logo img {
                height: 32px;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .content-container {
                padding: 15px;
            }
        }

        /* Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>

<body>
    <!-- Loading Screen - Matching user_ui.php style -->
    <div class="loading-screen" id="loadingScreen">
        <div class="loading-content">
            <div class="spinner">
                <div class="bounce1"></div>
                <div class="bounce2"></div>
                <div class="bounce3"></div>
            </div>
            <div class="loading-text">Loading...</div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header">
        <div class="container">
            <a href="index.php" class="logo">
                <img src="assets/img/Logo Bisnis Bengkel Otomotif (3).png" alt="Snack In Logo">
                <span>SNACK IN ADMIN</span>
            </a>
            <div class="header-actions">
                <div class="profile-dropdown">
                    <div class="profile-trigger" id="profileTrigger">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($current_user['username'] ?? 'A', 0, 1)); ?>
                        </div>
                        <span><?php echo htmlspecialchars($current_user['username'] ?? 'Admin'); ?></span>
                        <i class="fas fa-chevron-down" style="font-size: 12px; margin-left: 5px;"></i>
                    </div>
                    <div class="dropdown-menu-custom" id="profileDropdown">
                        <a href="user_ui.php" class="dropdown-item-custom website-item">
                            <i class="fas fa-globe"></i>
                            <span>Go to User Website</span>
                        </a>
                        <a href="logout.php" class="dropdown-item-custom logout-item">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Exit</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <script>
        // Enhanced loading functions matching user_ui.php
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

                // Prevent dropdown from closing when clicking inside
                profileDropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }

            // Hide loading screen when page is loaded
            setTimeout(hideLoading, 100);

            // Show loading on page navigation
            document.querySelectorAll('a:not([href^="#"]):not([href^="javascript:"]):not([target="_blank"])').forEach(link => {
                link.addEventListener('click', function(e) {
                    // Don't show loading for dropdown items or same page links
                    if (!this.closest('.dropdown-menu-custom') && this.href !== window.location.href) {
                        showLoading();
                    }
                });
            });

            // Show loading on form submission
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    // Only show loading if form is valid
                    if (this.checkValidity()) {
                        showLoading();
                    }
                });
            });

            // Show loading for specific buttons
            document.querySelectorAll('.btn:not(.btn-secondary):not([type="button"])').forEach(button => {
                button.addEventListener('click', function(e) {
                    // Don't show loading for cancel/secondary buttons
                    if (!this.classList.contains('btn-secondary') && !this.hasAttribute('data-no-loading')) {
                        setTimeout(showLoading, 50);
                    }
                });
            });
        });

        // Page visibility API to hide loading when page becomes visible
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                setTimeout(hideLoading, 100);
            }
        });

        // Hide loading on window load
        window.addEventListener('load', function() {
            setTimeout(hideLoading, 200);
        });
    </script>
