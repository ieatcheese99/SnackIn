<?php
// Check if user is logged in
session_start();
$isLoggedIn = isset($_SESSION['username']) ? true : false;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Snack In</title>
    <!-- Google Fonts: Inter and Outfit -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;700;800&display=swap"
        rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Reset and Base Styles */
        :root {
            --primary-color: #00227c;
            --secondary-color: #001a5e;
            --accent-color: #f48c06;
            --dark-blue: #00227c;
            --white: #ffffff;
            --orange: #f69e22;
            --light-bg: #fdfbf7;
            --text-dark: #2d3748;
            --text-muted: #718096;
            --radius-md: 12px;
            --radius-lg: 24px;
            --shadow-sm: 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 10px 25px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 20px 40px rgba(0, 0, 0, 0.15);
            --transition-fast: 0.2s ease;
            --transition-normal: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: var(--light-bg);
            background-image: radial-gradient(var(--orange) 0.5px, transparent 0.5px);
            background-size: 24px 24px;
            background-position: 0 0;
        }

        /* Preloader Styles */
        .preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--light-bg);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.6s ease, visibility 0.6s ease;
        }

        .preloader.fade-out {
            opacity: 0;
            visibility: hidden;
        }

        .loader {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 4px solid rgba(246, 158, 34, 0.2);
            border-top-color: var(--accent-color);
            border-right-color: var(--primary-color);
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        ul {
            list-style: none;
        }

        img {
            max-width: 100%;
            height: auto;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #00227c;
            color: white;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-align: center;
        }

        .btn:hover {
            background-color: #00227c;
            transform: translateY(-2px);
        }

        .section-title {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-title h2 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #00227c;
        }

        .section-title .divider {
            width: 80px;
            height: 4px;
            background-color: #f69e22;
            margin: 0 auto;
        }

        /* Main Header */
        .main-header {
            background-color: #00227c;
            color: white;
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .main-header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 700;
        }

        .logo img {
            height: 40px;
            width: auto;
        }

        .main-nav ul {
            display: flex;
            gap: 30px;
        }

        .main-nav a {
            font-weight: 500;
            transition: color 0.3s ease;
            position: relative;
        }

        .main-nav a:hover {
            color: rgba(255, 255, 255, 0.8);
        }

        .main-nav a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: white;
            transition: width 0.3s ease;
        }

        .main-nav a:hover::after {
            width: 100%;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .action-icon {
            position: relative;
            font-size: 18px;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .action-icon:hover {
            color: rgba(255, 255, 255, 0.8);
        }

        .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: white;
            color: #00227c;
            font-size: 10px;
            font-weight: 600;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-icon {
            color: #ff6b6b;
        }

        .mobile-menu-toggle {
            display: none;
            font-size: 24px;
            cursor: pointer;
        }

        /* About Hero Section */
        .about-hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .about-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 50vh;
            height: 50vh;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 60%);
            border-radius: 50%;
            animation: pulse 8s infinite alternate;
        }

        .about-hero h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 3.5rem;
            margin-bottom: 24px;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .about-hero p {
            font-size: 1.25rem;
            max-width: 800px;
            margin: 0 auto;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.7;
        }

        /* About Content Section */
        .about-content {
            padding: 100px 0;
            background-color: var(--light-bg);
        }

        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .about-image {
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-xl);
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            background: var(--white);
            padding: 40px;
        }

        .about-image img {
            max-height: 350px;
            object-fit: contain;
            transition: transform 0.5s ease;
        }

        .about-image:hover img {
            transform: scale(1.05);
        }

        .about-text h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 3rem;
            margin-bottom: 24px;
            color: var(--primary-color);
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -1px;
        }

        .about-text p {
            margin-bottom: 24px;
            font-size: 1.15rem;
            color: var(--text-muted);
            line-height: 1.8;
        }

        /* Our Story Section */
        .our-story {
            padding: 80px 0;
            background-color: #f9f9f9;
        }

        .story-timeline {
            position: relative;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px 0;
        }

        .story-timeline::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 50%;
            width: 4px;
            background-color: #f69e22;
            transform: translateX(-50%);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 60px;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-content {
            position: relative;
            width: 45%;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .timeline-item:nth-child(odd) .timeline-content {
            margin-left: auto;
        }

        .timeline-content::before {
            content: '';
            position: absolute;
            top: 20px;
            width: 20px;
            height: 20px;
            background-color: white;
            border: 4px solid #f69e22;
            border-radius: 50%;
        }

        .timeline-item:nth-child(odd) .timeline-content::before {
            left: -44px;
        }

        .timeline-item:nth-child(even) .timeline-content::before {
            right: -44px;
        }

        .timeline-date {
            display: inline-block;
            padding: 5px 15px;
            background-color: #f69e22;
            color: white;
            border-radius: 20px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .timeline-content h3 {
            margin-bottom: 10px;
            color: #00227c;
        }

        /* Our Values Section */
        .our-values {
            padding: 80px 0;
            background-color: white;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }

        .value-card {
            text-align: center;
            padding: 30px 20px;
            border-radius: 10px;
            background-color: #f9f9f9;
            transition: all 0.3s ease;
        }

        .value-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .value-icon {
            width: 80px;
            height: 80px;
            background-color: rgba(246, 158, 34, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .value-icon i {
            font-size: 36px;
            color: #f69e22;
        }

        .value-card h3 {
            margin-bottom: 15px;
            color: #00227c;
        }

        /* Team Section */
        .our-team {
            padding: 80px 0;
            background-color: #f9f9f9;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
        }

        .team-member {
            text-align: center;
        }

        .team-photo {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto 20px;
            border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .team-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .team-member h3 {
            margin-bottom: 5px;
            color: #00227c;
        }

        .team-position {
            color: #f69e22;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .team-social {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .team-social a {
            width: 36px;
            height: 36px;
            background-color: #00227c;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .team-social a:hover {
            background-color: #f69e22;
            transform: translateY(-3px);
        }

        /* Footer */
        .footer {
            background-color: #222;
            color: white;
            padding-top: 60px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-column h3 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #444;
        }

        .footer-column ul li {
            margin-bottom: 10px;
        }

        .footer-column a {
            transition: color 0.3s ease;
        }

        .footer-column a:hover {
            color: #f69e22;
        }

        .footer-contact li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 15px;
        }

        .footer-contact i {
            color: #888;
            margin-top: 5px;
        }

        .footer-newsletter p {
            margin-bottom: 20px;
            color: #ccc;
        }

        .newsletter-form {
            display: flex;
            gap: 10px;
        }

        .newsletter-form input {
            flex: 1;
            padding: 10px;
            border: none;
            background-color: #333;
            color: white;
            border-radius: 5px;
        }

        .footer-social {
            margin-top: 20px;
        }

        .footer-social h4 {
            font-size: 16px;
            margin-bottom: 15px;
        }

        .social-icons {
            display: flex;
            gap: 15px;
        }

        .social-icons a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: #333;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .social-icons a:hover {
            background-color: #f69e22;
            transform: translateY(-3px);
        }

        .footer-bottom {
            border-top: 1px solid #444;
            padding: 20px 0;
            text-align: center;
        }

        .footer-bottom p {
            color: #888;
            font-size: 14px;
        }

        .footer-bottom a {
            color: #f69e22;
        }

        /* Mobile Menu */
        .mobile-menu {
            position: fixed;
            top: 0;
            right: -300px;
            width: 300px;
            height: 100%;
            background-color: white;
            z-index: 2000;
            padding: 20px;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
            transition: right 0.3s ease;
            overflow-y: auto;
        }

        .mobile-menu.active {
            right: 0;
        }

        .mobile-menu-close {
            text-align: right;
            margin-bottom: 20px;
            font-size: 24px;
            cursor: pointer;
        }

        .mobile-menu ul li {
            margin-bottom: 15px;
        }

        .mobile-menu ul li a {
            display: block;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            color: #333;
            font-weight: 500;
        }

        .mobile-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1999;
            display: none;
        }

        .mobile-menu-overlay.active {
            display: block;
        }

        /* Login Popup Styles */
        .attractive-login-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.9);
            width: 90%;
            max-width: 400px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            z-index: 2100;
            overflow: hidden;
            transition: all 0.3s ease;
            opacity: 0;
        }

        .attractive-login-popup.active {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }

        .login-popup-content {
            padding: 30px 25px;
            text-align: center;
        }

        .login-popup-close {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 22px;
            cursor: pointer;
            color: #999;
            transition: color 0.3s;
        }

        .login-popup-close:hover {
            color: #333;
        }

        .login-popup-icon {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 80px;
            height: 80px;
            background-color: rgba(246, 158, 34, 0.15);
            border-radius: 50%;
            margin: 0 auto 20px;
        }

        .login-popup-icon i {
            font-size: 36px;
            color: #f69e22;
        }

        .attractive-login-popup h2 {
            margin-bottom: 10px;
            color: #00227c;
            font-size: 24px;
        }

        .attractive-login-popup p {
            margin-bottom: 25px;
            color: #666;
            font-size: 16px;
            line-height: 1.5;
        }

        .login-popup-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .btn-login,
        .btn-register {
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 500;
            font-size: 16px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-login {
            background-color: #00227c;
            color: white;
        }

        .btn-login:hover {
            background-color: #001a5e;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 34, 124, 0.3);
        }

        .btn-register {
            background-color: #f5f5f5;
            color: #333;
        }

        .btn-register:hover {
            background-color: #ebebeb;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .login-popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 2050;
            backdrop-filter: blur(3px);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .login-popup-overlay.active {
            opacity: 1;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .main-nav {
                display: none;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .about-grid,
            .values-grid,
            .team-grid {
                grid-template-columns: 1fr 1fr;
                gap: 30px;
            }

            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .about-grid {
                grid-template-columns: 1fr;
            }

            .about-image {
                order: -1;
            }

            .story-timeline::before {
                left: 30px;
            }

            .timeline-content {
                width: calc(100% - 60px);
                margin-left: 60px !important;
            }

            .timeline-item:nth-child(odd) .timeline-content::before,
            .timeline-item:nth-child(even) .timeline-content::before {
                left: -44px;
            }

            .values-grid {
                grid-template-columns: 1fr;
            }

            .team-grid {
                grid-template-columns: 1fr 1fr;
            }

            .footer-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .newsletter-form {
                flex-direction: column;
                gap: 10px;
            }

            .newsletter-form .btn {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .team-grid {
                grid-template-columns: 1fr;
            }

            .about-hero h1 {
                font-size: 2.2rem;
            }

            .about-hero p {
                font-size: 1rem;
            }

            .about-text h2 {
                font-size: 2rem;
            }
        }
    </style>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
</head>

<body>
    <!-- Preloader -->
    <div class="preloader" id="preloader">
        <div class="loader"></div>
    </div>

    <!-- Login Popup -->
    <?php if (!$isLoggedIn): ?>
        <div class="attractive-login-popup" id="login-popup">
            <div class="login-popup-content">
                <div class="login-popup-close">&times;</div>
                <div class="login-popup-icon">
                    <i class="fas fa-user-lock"></i>
                </div>
                <h2>Login Required</h2>
                <p>Please login to add items to your cart and access all features!</p>
                <div class="login-popup-buttons">
                    <a href="login.php" class="btn-login">Login Now</a>
                    <a href="register.php" class="btn-register">Register</a>
                </div>
            </div>
        </div>
        <div class="login-popup-overlay" id="login-popup-overlay"></div>
    <?php endif; ?>

    <!-- Main Header -->
    <header class="main-header">
        <div class="container">
            <a href="user_ui.php" class="logo">
                <img src="assets/img/Logo Bisnis Bengkel Otomotif (3).png" alt="Snack In Logo">
                <span>SNACK IN</span>
            </a>
            <nav class="main-nav">
                <ul>
                    <li><a href="user_ui.php">Home</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="user_ui.php#produk">Shop</a></li>
                    <li><a href="user_ui.php#testimonials">Testimoni</a></li>
                    <li><a href="user_ui.php#footer">Contact</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <div class="action-icon">
                    <a href="include/cart.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="badge cart-count">0</span>
                    </a>
                </div>
                <div class="action-icon">
                    <a href="history.php">
                        <i class="fas fa-history"></i>
                    </a>
                </div>
                <?php if ($isLoggedIn): ?>
                    <a href="logout.php" class="action-icon logout-icon">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="action-icon">
                        <i class="fas fa-user"></i>
                    </a>
                <?php endif; ?>
                <div class="mobile-menu-toggle" id="mobile-menu-toggle">
                    <i class="fas fa-bars"></i>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobile-menu">
        <div class="mobile-menu-close" id="mobile-menu-close">
            <i class="fas fa-times"></i>
        </div>
        <ul>
            <li><a href="user_ui.php">Home</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="user_ui.php#produk">Shop</a></li>
            <li><a href="user_ui.php#testimonials">Testimoni</a></li>
            <li><a href="user_ui.php#footer">Contact</a></li>
        </ul>
    </div>
    <div class="mobile-menu-overlay" id="mobile-menu-overlay"></div>

    <!-- About Hero Section -->
    <section class="about-hero">
        <div class="container">
            <h1>About Snack In</h1>
            <p>Discover the story behind your favorite snack store and our commitment to quality and taste.</p>
        </div>
    </section>

    <!-- About Content Section -->
    <section class="about-content">
        <div class="container">
            <div class="about-grid">
                <div class="about-image">
                    <img src="images/logo-tab.png" alt="Snack In Logo">
                </div>
                <div class="about-text">
                    <h2>Cerita Di Balik <span style="color: var(--accent-color);">Snack In</span></h2>
                    <p>Snack In lahir dari semangat untuk membawa inovasi di dunia kuliner ringan. Berawal dari ide
                        sederhana sekelompok siswa yang ingin menciptakan pengalaman ngemil yang berbeda, kini kami
                        telah berkembang menjadi brand snack lokal yang dicintai.</p>
                    <p>Setiap produk kami dibuat dengan bahan-bahan pilihan terbaik dan diolah dengan standar kualitas
                        yang ketat. Kami berkomitmen untuk terus berinovasi dan menghadirkan variasi rasa baru yang
                        menarik bagi pelanggan kami.</p>
                    <p>Dari makanan ringan tradisional Indonesia hingga minuman segar yang menyegarkan, Snack In hadir
                        untuk memenuhi kebutuhan cemilan Anda kapan saja dan di mana saja.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="our-story">
        <div class="container">
            <div class="section-title">
                <h2>Perjalanan Kami</h2>
                <div class="divider"></div>
            </div>
            <div class="story-timeline">
                <div class="timeline-item">
                    <div class="timeline-content">
                        <span class="timeline-date">2020</span>
                        <h3>Awal Mula</h3>
                        <p>Snack In didirikan sebagai usaha kecil di SMK Budi Luhur dengan hanya 5 jenis produk.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <span class="timeline-date">2021</span>
                        <h3>Ekspansi Produk</h3>
                        <p>Kami memperluas jangkauan produk dengan menambahkan berbagai minuman segar dan makanan ringan
                            tradisional.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <span class="timeline-date">2022</span>
                        <h3>Inovasi Digital</h3>
                        <p>Meluncurkan website dan sistem pemesanan online untuk memudahkan pelanggan menikmati produk
                            kami.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <span class="timeline-date">2023</span>
                        <h3>Kolaborasi Brand</h3>
                        <p>Berkolaborasi dengan brand ambassador lokal untuk mempromosikan produk kami ke pasar yang
                            lebih luas.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <span class="timeline-date">2024</span>
                        <h3>Saat Ini</h3>
                        <p>Terus berkembang dengan fokus pada kualitas, inovasi, dan kepuasan pelanggan.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Values Section -->
    <section class="our-values">
        <div class="container">
            <div class="section-title">
                <h2>Nilai-Nilai Kami</h2>
                <div class="divider"></div>
            </div>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>Kualitas</h3>
                    <p>Kami hanya menggunakan bahan-bahan berkualitas terbaik untuk menghasilkan produk yang lezat dan
                        sehat.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h3>Inovasi</h3>
                    <p>Kami terus berinovasi untuk menghadirkan variasi rasa baru yang menarik bagi pelanggan kami.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Kepuasan Pelanggan</h3>
                    <p>Kepuasan pelanggan adalah prioritas utama kami dalam setiap aspek bisnis.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="our-team">
        <div class="container">
            <div class="section-title">
                <h2>Tim Kami</h2>
                <div class="divider"></div>
            </div>
            <div class="team-grid">
                <div class="team-member">
                    <div class="team-photo">
                        <img src="assets/img/team-1.jpg" alt="Team Member 1">
                    </div>
                    <h3>Ahmad Fauzi</h3>
                    <div class="team-position">Founder & CEO</div>
                    <div class="team-social">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                <div class="team-member">
                    <div class="team-photo">
                        <img src="assets/img/team-2.jpg" alt="Team Member 2">
                    </div>
                    <h3>Siti Rahayu</h3>
                    <div class="team-position">Head of Production</div>
                    <div class="team-social">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                <div class="team-member">
                    <div class="team-photo">
                        <img src="assets/img/team-3.jpg" alt="Team Member 3">
                    </div>
                    <h3>Budi Santoso</h3>
                    <div class="team-position">Marketing Manager</div>
                    <div class="team-social">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                <div class="team-member">
                    <div class="team-photo">
                        <img src="assets/img/team-4.jpg" alt="Team Member 4">
                    </div>
                    <h3>Dewi Lestari</h3>
                    <div class="team-position">Customer Relations</div>
                    <div class="team-social">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <h3>Tentang Snack In</h3>
                    <p style="color: #ccc; margin-bottom: 20px;">Snack In adalah platform e-commerce yang menyediakan
                        berbagai macam cemilan lezat untuk menemani hari-hari Anda.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>

                <div class="footer-column">
                    <h3>Tautan Cepat</h3>
                    <ul>
                        <li><a href="user_ui.php">Beranda</a></li>
                        <li><a href="user_ui.php#produk">Produk</a></li>
                        <li><a href="About.php">Tentang Kami</a></li>
                        <li><a href="#">Cara Pesan</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h3>Lokasi Kami</h3>
                    <ul class="footer-contact">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>SMA & SMK Budi Luhur<br>Jl. Raden Saleh No. 999<br>Karang Tengah, Kota
                                Tangerang</span>
                        </li>
                    </ul>
                    <div style="margin-top: 15px; border-radius: 8px; overflow: hidden; border: 2px solid #444;">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.4276185859345!2d106.71180807567793!3d-6.207191193780366!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f82d2ebbe9ad%3A0xc3f8376ac4ab9c!2sYayasan%20Pendidikan%20Budi%20Luhur%20(YPBL)!5e0!3m2!1sen!2sid!4v1709121287968!5m2!1sen!2sid"
                            width="100%" height="150" style="border:0;" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>

                <div class="footer-column">
                    <h3>Hubungi Kami</h3>
                    <ul class="footer-contact">
                        <li>
                            <i class="fas fa-phone"></i>
                            <span>+62 812 3456 7890</span>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2025 Snack In. All rights reserved. | Designed by <a href="#">Team SnackIn</a></p>
            </div>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function () {
            // Check if user is logged in
            const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;

            // Function to show attractive login popup
            function showLoginPopup() {
                $('#login-popup').addClass('active').fadeIn(100);
                $('#login-popup-overlay').addClass('active').fadeIn(100);
                $('body').css('overflow', 'hidden');
                return false;
            }

            // Function to hide login popup
            function hideLoginPopup() {
                $('#login-popup').removeClass('active').fadeOut(200);
                $('#login-popup-overlay').removeClass('active').fadeOut(200);
                $('body').css('overflow', '');
            }

            // Close login popup when clicking close button or overlay
            $('.login-popup-close, #login-popup-overlay').click(function () {
                hideLoginPopup();
            });

            // Prevent propagation when clicking on popup content to avoid closing
            $('.login-popup-content').click(function (e) {
                e.stopPropagation();
            });

            // Handle cart icon click
            $('.action-icon a[href="include/cart.php"]').click(function (e) {
                if (!isLoggedIn) {
                    e.preventDefault();
                    return showLoginPopup();
                }
            });

            // Handle history link click
            $('.action-icon a[href="history.php"]').click(function (e) {
                if (!isLoggedIn) {
                    e.preventDefault();
                    return showLoginPopup();
                }
            });

            // Update cart count
            function updateCartCount() {
                $.ajax({
                    url: "include/cart_action.php",
                    type: "POST",
                    data: {
                        action: "count"
                    },
                    success: function (count) {
                        $(".cart-count").text(count);
                    }
                });
            }

            // Initialize cart count
            updateCartCount();

            // Mobile menu functionality
            const mobileMenuToggle = document.getElementById("mobile-menu-toggle");
            const mobileMenu = document.getElementById("mobile-menu");
            const mobileMenuClose = document.getElementById("mobile-menu-close");
            const mobileMenuOverlay = document.getElementById("mobile-menu-overlay");

            // Toggle mobile menu
            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener("click", () => {
                    if (mobileMenu) {
                        mobileMenu.style.right = "0";
                        if (mobileMenuOverlay) {
                            mobileMenuOverlay.style.display = "block";
                        }
                        document.body.style.overflow = "hidden"; // Prevent scrolling
                    }
                });
            }

            // Close mobile menu
            function closeMobileMenu() {
                if (mobileMenu) {
                    mobileMenu.style.right = "-300px";
                    if (mobileMenuOverlay) {
                        mobileMenuOverlay.style.display = "none";
                    }
                    document.body.style.overflow = ""; // Enable scrolling
                }
            }

            if (mobileMenuClose) {
                mobileMenuClose.addEventListener("click", closeMobileMenu);
            }

            if (mobileMenuOverlay) {
                mobileMenuOverlay.addEventListener("click", closeMobileMenu);
            }
        });

        // Initialize preloader
        document.addEventListener('DOMContentLoaded', () => {
            const preloader = document.getElementById('preloader');
            if (preloader) {
                setTimeout(() => {
                    preloader.classList.add('fade-out');
                }, 400); // Small delay to ensure smooth transition
            }
        });
    </script>
</body>

</html>