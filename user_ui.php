<?php
require "config/database.php";

// Check if user is logged in
session_start();
$isLoggedIn = isset($_SESSION['username']) ? true : false;

// Fetch all products with their category information and stock
$queryProduk = mysqli_query($db, "SELECT b.id, b.nama, b.Harga as harga, b.gambar, b.Deskripsi as deskripsi, b.Stok, k.nama as kategori 
                                 FROM barang b 
                                 JOIN kategori k ON b.kategori_id = k.id 
                                 WHERE b.Stok > 0
                                 ORDER BY b.id DESC");

// Fetch distinct categories for the filter
$queryKategori = mysqli_query($db, "SELECT id, nama FROM kategori ORDER BY nama");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snack In - Your Premium Snack Store</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/img/Logo Bisnis Bengkel Otomotif (3).png">
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

        html {
            scroll-behavior: smooth;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: var(--light-bg);
            /* Add subtle pattern */
            background-image: radial-gradient(var(--orange) 0.5px, transparent 0.5px);
            background-size: 24px 24px;
            background-position: 0 0;
            background-color: #fdfbf7;
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
            background-color: #001a5e;
            transform: translateY(-2px);
        }

        .btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
            position: relative;
        }

        .section-title h2 {
            font-size: 36px;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 15px;
            letter-spacing: -0.5px;
        }

        .section-title p {
            color: var(--text-muted);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .section-title .divider {
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--orange), var(--accent-color));
            margin: 20px auto 0;
            border-radius: 4px;
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            height: auto;
            min-height: 500px;
            overflow: hidden;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
        }

        .hero-slider {
            width: 100%;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .hero-slides {
            display: flex;
            height: 100%;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hero-slide {
            position: relative;
            flex: 0 0 100%;
            height: 100%;
            display: flex;
            align-items: center;
            padding: 40px 5%;
            overflow: hidden;
            box-sizing: border-box;
        }

        .hero-slide::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 80vh;
            height: 80vh;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 60%);
            border-radius: 50%;
            animation: pulse 8s infinite alternate;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 0.5;
            }

            100% {
                transform: scale(1.2);
                opacity: 0.8;
            }
        }

        .hero-content {
            width: 50%;
            position: relative;
            z-index: 2;
            padding: 20px 40px;
        }

        .hero-content h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 4.5rem;
            margin-bottom: 24px;
            color: var(--white);
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -1px;
        }

        .hero-content h1 span {
            color: var(--accent-color);
            position: relative;
            display: inline-block;
        }

        .hero-content h1 span::after {
            content: '';
            position: absolute;
            bottom: 8px;
            left: 0;
            width: 100%;
            height: 12px;
            background-color: rgba(244, 140, 6, 0.4);
            z-index: -1;
            transform: skewX(-15deg);
        }

        .hero-content p {
            font-size: 1.25rem;
            margin-bottom: 40px;
            max-width: 500px;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.7;
        }

        .hero-btn {
            display: inline-flex;
            align-items: center;
            padding: 16px 36px;
            background: linear-gradient(135deg, var(--accent-color), var(--orange));
            color: var(--white);
            font-family: 'Outfit', sans-serif;
            text-decoration: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 700;
            transition: var(--transition-normal);
            box-shadow: 0 10px 20px rgba(246, 158, 34, 0.3);
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .hero-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.5s;
        }

        .hero-btn:hover::before {
            left: 100%;
        }

        .hero-btn .arrow-icon {
            margin-left: 12px;
            font-style: normal;
            transition: transform 0.3s ease;
            font-size: 1.2rem;
        }

        .hero-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(246, 158, 34, 0.4);
        }

        .hero-btn:hover .arrow-icon {
            transform: translateX(8px);
        }

        .hero-image {
            position: absolute;
            right: 5%;
            top: 50%;
            transform: translateY(-50%);
            width: 45%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }

        .hero-image img {
            max-width: 100%;
            height: auto;
            max-height: 400px;
            filter: drop-shadow(0 20px 30px rgba(0, 0, 0, 0.3));
            animation: floatHero 6s ease-in-out infinite;
            transform-origin: center;
        }

        @keyframes floatHero {
            0% {
                transform: translateY(0px) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(2deg);
            }

            100% {
                transform: translateY(0px) rotate(0deg);
            }
        }

        /* Slider Navigation */
        .slider-nav {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            gap: 10px;
            z-index: 10;
        }

        .slider-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .slider-dot.active {
            background-color: var(--accent-color);
            transform: scale(1.2);
        }

        .slider-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            cursor: pointer;
            z-index: 10;
            transition: all 0.3s ease;
        }

        .slider-arrow:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }

        .slider-prev {
            left: 20px;
        }

        .slider-next {
            right: 20px;
        }

        /* Product List Section */
        .daftar-produk-section {
            margin: 0 calc(50% - 50vw);
            background-color: var(--orange);
            padding: 60px 0;
            width: 100vw;
            position: relative;
            left: 50%;
            transform: translateX(-50%);
        }

        .daftar-produk {
            padding: 0 20px;
        }

        .daftar-produk h2 {
            font-family: 'Outfit', sans-serif;
            text-align: center;
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            color: var(--primary-color);
        }

        /* Category Filter */
        .category-filter {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-bottom: 50px;
            flex-wrap: wrap;
        }

        .category-btn {
            padding: 10px 24px;
            background-color: var(--white);
            border: 2px solid transparent;
            border-radius: 50px;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 15px;
            color: var(--text-muted);
            cursor: pointer;
            transition: var(--transition-normal);
            box-shadow: var(--shadow-sm);
        }

        .category-btn:hover {
            color: var(--primary-color);
            background-color: var(--white);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .category-btn.active {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            box-shadow: 0 8px 20px rgba(0, 34, 124, 0.2);
            border-color: transparent;
        }

        .produk-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .produk-card {
            background: var(--white);
            border-radius: 0;
            padding: 0;
            text-align: left;
            box-shadow: var(--shadow-sm);
            transition: var(--transition-normal);
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .produk-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
        }

        .produk-card .image-container {
            position: relative;
            margin-bottom: 0;
            overflow: hidden;
            border-radius: 0;
        }

        .produk-card img {
            width: 100%;
            height: 240px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .produk-card:hover img {
            transform: scale(1.08);
        }

        .produk-card .card-content {
            padding: 24px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .produk-card h3 {
            margin: 0 0 8px 0;
            font-family: 'Outfit', sans-serif;
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1.3;
        }

        .produk-card .price {
            color: var(--accent-color);
            font-size: 1.4rem;
            font-weight: 800;
            margin-bottom: 15px;
            font-family: 'Outfit', sans-serif;
        }

        .produk-card .description {
            color: var(--text-muted);
            margin-bottom: 20px;
            flex-grow: 1;
            line-height: 1.6;
            font-size: 0.95rem;
        }

        .produk-card .btn {
            width: 100%;
            padding: 12px;
            border-radius: 0;
            background: var(--primary-color);
            color: var(--white);
            border: 2px solid var(--primary-color);
            font-weight: 600;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            transition: var(--transition-normal);
        }

        .produk-card .btn:hover {
            background: var(--white);
            color: var(--primary-color);
        }

        .category-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.95);
            color: var(--primary-color);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            z-index: 2;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(4px);
        }

        .stock-info {
            color: var(--text-dark);
            font-size: 13px;
            margin-bottom: 15px;
            padding: 8px 12px;
            background-color: var(--light-bg);
            border-radius: 8px;
            font-weight: 500;
            display: inline-block;
        }

        .stock-low {
            color: #e53e3e;
            background-color: #fff5f5;
        }

        .stock-out {
            color: #c53030;
            background-color: #fed7d7;
            text-decoration: line-through;
        }

        /* Cart Popup Styles */
        .cart-popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(15, 23, 42, 0.6);
            z-index: 3000;
            backdrop-filter: blur(8px);
        }

        .cart-popup-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.95);
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 0;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: popupSlideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
        }

        @keyframes popupSlideIn {
            from {
                opacity: 0;
                transform: translate(-50%, -45%) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }

        .cart-popup-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-popup-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            letter-spacing: -0.5px;
        }

        .cart-popup-close {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            padding: 0;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: var(--transition-fast);
        }

        .cart-popup-close:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: rotate(90deg);
        }

        .cart-popup-body {
            padding: 30px;
        }

        .product-info {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        .product-image {
            width: 90px;
            height: 90px;
            border-radius: var(--radius-md);
            object-fit: cover;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: var(--shadow-sm);
        }

        .product-details h4 {
            margin: 0 0 6px 0;
            font-size: 18px;
            font-weight: 700;
            color: var(--text-dark);
            font-family: 'Outfit', sans-serif;
        }

        .product-price {
            font-size: 20px;
            font-weight: 800;
            color: var(--accent-color);
            margin-bottom: 8px;
            font-family: 'Outfit', sans-serif;
        }

        .quantity-section {
            margin-bottom: 30px;
        }

        .quantity-section label {
            display: block;
            margin-bottom: 12px;
            font-weight: 600;
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .quantity-btn {
            width: 44px;
            height: 44px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            background: var(--white);
            color: var(--text-dark);
            border-radius: 50%;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition-fast);
            box-shadow: var(--shadow-sm);
        }

        .quantity-btn:hover:not(:disabled) {
            background: var(--primary-color);
            color: var(--white);
            border-color: var(--primary-color);
        }

        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f1f5f9;
        }

        .quantity-input {
            width: 70px;
            height: 44px;
            text-align: center;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: var(--radius-md);
            font-size: 16px;
            font-weight: 700;
            color: var(--text-dark);
            background: #f8fafc;
        }

        .total-section {
            background: #f8fafc;
            padding: 24px;
            border-radius: var(--radius-md);
            margin-bottom: 30px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            color: var(--text-muted);
        }

        .total-row:last-child {
            margin-bottom: 0;
            padding-top: 15px;
            border-top: 1px dashed rgba(0, 0, 0, 0.1);
            font-weight: 800;
            font-size: 20px;
            color: var(--text-dark);
            font-family: 'Outfit', sans-serif;
        }

        .cart-popup-actions {
            display: flex;
            gap: 16px;
        }

        .btn-secondary {
            flex: 1;
            padding: 14px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            background: var(--white);
            color: var(--text-dark);
            border-radius: var(--radius-md);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-fast);
        }

        .btn-secondary:hover {
            background: #f1f5f9;
            color: var(--text-dark);
        }

        .btn-primary {
            flex: 2;
            padding: 14px;
            background: linear-gradient(135deg, var(--accent-color), var(--orange));
            color: var(--white);
            border: none;
            border-radius: var(--radius-md);
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition-normal);
            box-shadow: 0 4px 15px rgba(246, 158, 34, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(246, 158, 34, 0.4);
        }

        /* Login Popup Styles */
        .attractive-login-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.95);
            width: 90%;
            max-width: 420px;
            background-color: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            z-index: 2100;
            overflow: hidden;
            transition: var(--transition-normal);
            opacity: 0;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .attractive-login-popup.active {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }

        .login-popup-content {
            padding: 40px 30px;
            text-align: center;
        }

        .login-popup-close {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 20px;
            cursor: pointer;
            color: var(--text-muted);
            transition: var(--transition-fast);
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #f1f5f9;
        }

        .login-popup-close:hover {
            color: var(--text-dark);
            background: #e2e8f0;
            transform: rotate(90deg);
        }

        .login-popup-icon {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, rgba(246, 158, 34, 0.15), rgba(244, 140, 6, 0.25));
            border-radius: 50%;
            margin: 0 auto 25px;
            box-shadow: 0 10px 25px rgba(246, 158, 34, 0.1);
        }

        .login-popup-icon i {
            font-size: 40px;
            color: var(--accent-color);
        }

        .attractive-login-popup h2 {
            margin-bottom: 12px;
            color: var(--primary-color);
            font-size: 28px;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .attractive-login-popup p {
            margin-bottom: 30px;
            color: var(--text-muted);
            font-size: 16px;
            line-height: 1.6;
        }

        .login-popup-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-direction: column;
        }

        .btn-login,
        .btn-register {
            padding: 14px 28px;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 16px;
            transition: var(--transition-normal);
            text-decoration: none;
            width: 100%;
            font-family: 'Inter', sans-serif;
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            box-shadow: 0 4px 15px rgba(0, 34, 124, 0.2);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 34, 124, 0.3);
        }

        .btn-register {
            background-color: var(--white);
            color: var(--text-dark);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .btn-register:hover {
            background-color: #f8fafc;
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .login-popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(15, 23, 42, 0.6);
            z-index: 2050;
            backdrop-filter: blur(8px);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .login-popup-overlay.active {
            opacity: 1;
        }

        /* Success Popup Styles */
        .success-popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 3000;
            backdrop-filter: blur(5px);
        }

        .success-popup-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 16px;
            padding: 40px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: successSlideIn 0.5s ease-out;
        }

        @keyframes successSlideIn {
            from {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.8);
            }

            to {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }

        .success-checkmark {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
            display: flex;
            align-items: center;
            justify-content: center;
            animation: checkmarkPulse 0.6s ease-out;
        }

        @keyframes checkmarkPulse {
            0% {
                transform: scale(0);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        .success-checkmark i {
            color: white;
            font-size: 36px;
        }

        .success-title {
            font-size: 24px;
            font-weight: 700;
            color: #00227c;
            margin-bottom: 15px;
        }

        .success-message {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .success-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        /* Testimonials Section */
        .testimonials {
            padding: 80px 0;
            background-color: #00227c;
            color: white;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
        }

        .testimonial-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 30px 20px;
            transition: all 0.3s ease;
        }

        .testimonial-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .testimonial-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;
            margin-bottom: 20px;
            border: 4px solid #f69e22;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            background: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 12px;
            position: relative;
        }

        .testimonial-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .testimonial-image .fallback-text {
            display: none;
            font-weight: 600;
            color: #666;
            text-align: center;
            padding: 10px;
        }

        .testimonial-image.image-error {
            background: linear-gradient(135deg, #f0f0f0, #e0e0e0);
        }

        .testimonial-image.image-error .fallback-text {
            display: block;
        }

        .testimonial-image.image-error img {
            display: none;
        }

        .testimonial-stars {
            color: #f69e22;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .testimonial-text {
            margin-bottom: 15px;
            font-style: italic;
        }

        .testimonial-name {
            font-weight: 600;
            font-size: 18px;
        }

        /* Footer */
        .footer {
            background-color: #222;
            color: white;
            padding-top: 60px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

        /* Notification */
        #cart-notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-20px);
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s, transform 0.3s;
            min-width: 300px;
            max-width: 80%;
            text-align: center;
            font-size: 18px;
            font-weight: 500;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .hero-section {
                min-height: 500px;
                height: auto;
            }

            .hero-content h1 {
                font-size: 3rem;
            }

            .hero-content p {
                font-size: 1.1rem;
            }

            .produk-container {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .hero-section {
                min-height: 450px;
                height: auto;
            }

            .hero-slide {
                flex-direction: column;
                justify-content: center;
                text-align: center;
                padding: 20px;
            }

            .hero-content {
                width: 100%;
                padding: 0;
                margin-bottom: 20px;
            }

            .hero-content h1 {
                font-size: 2rem;
                margin-bottom: 15px;
            }

            .hero-content p {
                margin: 0 auto 20px;
            }

            .hero-image {
                position: relative;
                width: 100%;
                right: 0;
                justify-content: center;
                height: auto;
                max-height: 200px;
            }

            .hero-image img {
                max-height: 180px;
            }

            .produk-container {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 15px;
            }

            .cart-popup-content {
                width: 95%;
                max-height: 95vh;
            }

            .cart-popup-body {
                padding: 20px;
            }

            .product-info {
                flex-direction: column;
                text-align: center;
            }

            .cart-popup-actions {
                flex-direction: column;
            }
        }

        @media (max-width: 576px) {
            .produk-container {
                grid-template-columns: 1fr;
                padding: 0 10px;
            }

            .hero-content h1 {
                font-size: 1.8rem;
            }

            .hero-content p {
                font-size: 0.9rem;
                margin-bottom: 15px;
            }

            .hero-btn {
                padding: 10px 20px;
                font-size: 0.9rem;
            }

            .hero-image {
                max-height: 150px;
            }

            .hero-image img {
                max-height: 140px;
            }

            .category-filter {
                gap: 10px;
            }

            .category-btn {
                padding: 6px 15px;
                font-size: 14px;
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

    <!-- Include User Header -->
    <?php include 'include/user_header.php'; ?>

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

    <!-- Cart Popup -->
    <div class="cart-popup" id="cart-popup">
        <div class="cart-popup-content">
            <div class="cart-popup-header">
                <h3><i class="fas fa-shopping-cart"></i> Add to Cart</h3>
                <button class="cart-popup-close" id="cart-popup-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="cart-popup-body">
                <div class="product-info">
                    <img src="/placeholder.svg?height=80&width=80" alt="" class="product-image"
                        id="popup-product-image">
                    <div class="product-details">
                        <h4 id="popup-product-name"></h4>
                        <div class="product-price" id="popup-product-price"></div>
                        <div class="product-stock" id="popup-product-stock"></div>
                    </div>
                </div>

                <div class="quantity-section">
                    <label>Quantity:</label>
                    <div class="quantity-controls">
                        <button class="quantity-btn" id="quantity-minus">-</button>
                        <input type="number" class="quantity-input" id="quantity-input" value="1" min="1">
                        <button class="quantity-btn" id="quantity-plus">+</button>
                    </div>
                </div>

                <div class="total-section">
                    <div class="total-row">
                        <span>Price per item:</span>
                        <span id="price-per-item"></span>
                    </div>
                    <div class="total-row">
                        <span>Quantity:</span>
                        <span id="total-quantity">1</span>
                    </div>
                    <div class="total-row">
                        <span>Total:</span>
                        <span id="total-price"></span>
                    </div>
                </div>

                <div class="cart-popup-actions">
                    <button class="btn-secondary" id="continue-shopping">Continue Shopping</button>
                    <button class="btn-primary" id="add-to-cart-confirm">Add to Cart</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Popup -->
    <div class="success-popup" id="success-popup">
        <div class="success-popup-content">
            <div class="success-checkmark">
                <i class="fas fa-check"></i>
            </div>
            <h2 class="success-title">Order Successful!</h2>
            <p class="success-message">Your order has been placed successfully. You will receive a confirmation shortly.
            </p>
            <div class="success-actions">
                <button class="btn-secondary" onclick="hideSuccessPopup()">Continue Shopping</button>
                <a href="history.php" class="btn-primary">View Orders</a>
            </div>
        </div>
    </div>

    <!-- Hero Slider Section -->
    <section class="hero-section">
        <div class="hero-slider" id="hero-slider">
            <div class="hero-slides" id="hero-slides">
                <div class="hero-slide" style="background: linear-gradient(135deg, #00227c 0%, #0a3baf 100%);">
                    <div class="hero-content">
                        <h1>TEMUKAN KELEZATAN PREMIUM</h1>
                        <p>Jelajahi berbagai produk makanan ringan berkualitas tinggi dari Snack In. Nikmati sensasi
                            rasa yang tak terlupakan dan kualitas terbaik.</p>
                        <a href="#produk" class="hero-btn">
                            <span>Lihat Produk</span>
                            <i class="arrow-icon">→</i>
                        </a>
                    </div>
                    <div class="hero-image">
                        <img src="assets\img\pngtree-creamy-oreo-milkshake-png-image_13066217.png" alt="Hero Image 1">
                    </div>
                </div>
                <div class="hero-slide" style="background: linear-gradient(135deg, #00227c 0%, #0a3baf 100%);">
                    <div class="hero-content">
                        <h1>CEMILAN ASLI INDONESIA</h1>
                        <p>Diolah dengan resep tradisional yang dipadukan dengan teknologi modern. Rasakan kelezatan
                            asli Indonesia dalam setiap gigitannya.</p>
                        <a href="#produk" class="hero-btn">
                            <span>Tentang Kami</span>
                            <i class="arrow-icon">→</i>
                        </a>
                    </div>
                    <div class="hero-image">
                        <img src="assets\img\1c170f803582a8a3718df5543b206aad-Photoroom.png" alt="Hero Image 2">
                    </div>
                </div>
                <div class="hero-slide" style="background: linear-gradient(135deg, #00227c 0%, #0a3baf 100%);">
                    <div class="hero-content">
                        <h1>KUALITAS TERBAIK</h1>
                        <p>Kami hanya menggunakan bahan-bahan berkualitas terbaik untuk menghasilkan produk yang lezat
                            dan sehat untuk dinikmati bersama keluarga.</p>
                        <a href="#produk" class="hero-btn">
                            <span>Belanja Sekarang</span>
                            <i class="arrow-icon">→</i>
                        </a>
                    </div>
                    <div class="hero-image">
                        <img src="assets\img\pngtree-a-glass-of-iced-tea-isolated-on-transparent-background-ai-generated-png-image_11904777.png "
                            alt="Hero Image 3">
                    </div>
                </div>
            </div>
            <div class="slider-nav" id="slider-nav"></div>
            <div class="slider-arrow slider-prev" id="slider-prev">
                <i class="fas fa-chevron-left"></i>
            </div>
            <div class="slider-arrow slider-next" id="slider-next">
                <i class="fas fa-chevron-right"></i>
            </div>
        </div>
    </section>

    <section class="daftar-produk-section" id="produk">
        <div class="container daftar-produk">
            <h2>Daftar Produk</h2>

            <!-- Category Filter Buttons -->
            <div class="category-filter">
                <button class="category-btn active" data-category="all">Semua</button>
                <?php
                mysqli_data_seek($queryKategori, 0); // Reset pointer
                while ($kategori = mysqli_fetch_assoc($queryKategori)) { ?>
                    <button class="category-btn" data-category="<?php echo htmlspecialchars($kategori['nama']); ?>">
                        <?php echo htmlspecialchars($kategori['nama']); ?>
                    </button>
                <?php } ?>
            </div>

            <div class="produk-container" id="produk-container">
                <!-- Products will be loaded dynamically from PHP -->
                <?php while ($row = mysqli_fetch_assoc($queryProduk)) { ?>
                    <div class="produk-card" data-category="<?php echo htmlspecialchars($row['kategori']); ?>">
                        <div class="image-container">
                            <img src="<?php echo htmlspecialchars($row['gambar']); ?>"
                                alt="<?php echo htmlspecialchars($row['nama']); ?>" class="produk-img">
                            <span class="category-badge"><?php echo htmlspecialchars($row['kategori']); ?></span>
                        </div>
                        <h3><?php echo htmlspecialchars($row['nama']); ?></h3>
                        <div class="price">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></div>
                        <p class="description"><?php echo htmlspecialchars(substr($row['deskripsi'], 0, 80)); ?>...</p>
                        <div
                            class="stock-info <?php echo $row['Stok'] <= 5 ? ($row['Stok'] == 0 ? 'stock-out' : 'stock-low') : ''; ?>">
                            <?php if ($row['Stok'] == 0): ?>
                                Out of Stock
                            <?php elseif ($row['Stok'] <= 5): ?>
                                Only <?php echo $row['Stok']; ?> left in stock!
                            <?php else: ?>
                                Stock: <?php echo $row['Stok']; ?> items
                            <?php endif; ?>
                        </div>
                        <button class="btn add-to-cart" data-id="<?php echo $row['id']; ?>"
                            data-nama="<?php echo htmlspecialchars($row['nama']); ?>"
                            data-harga="<?php echo $row['harga']; ?>"
                            data-gambar="<?php echo htmlspecialchars($row['gambar']); ?>"
                            data-stok="<?php echo $row['Stok']; ?>" <?php echo $row['Stok'] == 0 ? 'disabled' : ''; ?>>
                            <?php echo $row['Stok'] == 0 ? 'Out of Stock' : 'Tambah ke Keranjang'; ?>
                        </button>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials" id="testimonials">
        <div class="container">
            <div class="section-title">
                <h2>Testimonial Pelanggan</h2>
                <div class="divider"></div>
            </div>
            <div class="testimonials-grid">
                <!-- Testimonial 1 -->
                <div class="testimonial-item">
                    <div class="testimonial-image">
                        <img src="images/pembeli/4.png" alt="Abidilah Akmal"
                            onerror="this.style.display='none'; this.parentElement.classList.add('image-error');">
                        <div class="fallback-text">Abidilah<br>Akmal</div>
                    </div>
                    <div class="testimonial-stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"Snack In adalah tempat favorit saya untuk membeli cemilan. Thai Tea
                        mereka sangat enak dan menyegarkan!"</p>
                    <h3 class="testimonial-name">Abidilah Akmal</h3>
                </div>

                <!-- Testimonial 2 -->
                <div class="testimonial-item">
                    <div class="testimonial-image">
                        <img src="images/pembeli/3.png" alt="Naila Aribah"
                            onerror="this.style.display='none'; this.parentElement.classList.add('image-error');">
                        <div class="fallback-text">Naila<br>Aribah</div>
                    </div>
                    <div class="testimonial-stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"Saya selalu membeli Capuccino dari Snack In setiap kali berkunjung.
                        Rasanya sangat Enak dan harganya terjangkau!"</p>
                    <h3 class="testimonial-name">Naila Aribah</h3>
                </div>

                <!-- Testimonial 3 -->
                <div class="testimonial-item">
                    <div class="testimonial-image">
                        <img src="images/pembeli/2.png" alt="Ahmad Fauzi"
                            onerror="this.style.display='none'; this.parentElement.classList.add('image-error');">
                        <div class="fallback-text">Ahmad<br>Fauzi</div>
                    </div>
                    <div class="testimonial-stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"Basreng dari Snack In adalah yang terbaik! Renyah, pedas, dan sangat
                        cocok untuk camilan saat nonton film."</p>
                    <h3 class="testimonial-name">Ahmad Fauzi</h3>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Company Info -->
                <div class="footer-column">
                    <h3>Snack In</h3>
                    <ul class="footer-contact">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>SMK BUDI LUHUR</span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <a href="tel:+6281211734491">0812-1173-4491</a>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:mz.siradj@gmail.com">mz.siradj@gmail.com</a>
                        </li>
                    </ul>
                    <div class="footer-social">
                        <h4>Follow Us</h4>
                        <div class="social-icons">
                            <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Products -->
                <div class="footer-column">
                    <h3>Products</h3>
                    <ul>
                        <li><a href="#">Makanan</a></li>
                        <li><a href="#">Minuman</a></li>
                    </ul>
                </div>

                <!-- Further Info -->
                <div class="footer-column">
                    <h3>Further Info</h3>
                    <ul>
                        <li><a href="user_ui.php">Home</a></li>

                        <li><a href="#produk">Shop</a></li>
                        <li><a href="#testimonials">Testimoni</a></li>
                        <li><a href="#footer">Contact</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2025 Snack In. All rights reserved. | Designed by <a href="#">Team SnackIn</a></p>
            </div>
        </div>
    </footer>

    <!-- Notification Element -->
    <div id="cart-notification"></div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Preloader functionality
        document.addEventListener('DOMContentLoaded', () => {
            const preloader = document.getElementById('preloader');
            if (preloader) {
                setTimeout(() => {
                    preloader.classList.add('fade-out');
                    // Optional: Remove preloader from DOM after transition
                    preloader.addEventListener('transitionend', () => {
                        preloader.remove();
                    });
                }, 400); // Small delay to ensure smooth transition
            }
            // Assuming fetchCartCount() is a global function or defined elsewhere
            // If it's part of the jQuery ready block, it will be called there.
            // If it needs to run on DOMContentLoaded, define it globally or here.
            // fetchCartCount(); 
        });

        $(document).ready(function () {
            // Hero Slider Functionality
            const slides = document.querySelectorAll('.hero-slide');
            const slidesContainer = document.getElementById('hero-slides');
            const sliderNav = document.getElementById('slider-nav');
            const prevBtn = document.getElementById('slider-prev');
            const nextBtn = document.getElementById('slider-next');
            let currentSlide = 0;
            let slideInterval;
            const slideCount = slides.length;

            // Create navigation dots
            for (let i = 0; i < slideCount; i++) {
                const dot = document.createElement('div');
                dot.classList.add('slider-dot');
                if (i === 0) dot.classList.add('active');
                dot.addEventListener('click', () => {
                    goToSlide(i);
                    resetInterval();
                });
                sliderNav.appendChild(dot);
            }

            // Initialize slider
            function initSlider() {
                updateSlider();
                startAutoSlide();

                prevBtn.addEventListener('click', () => {
                    goToSlide(currentSlide - 1);
                    resetInterval();
                });

                nextBtn.addEventListener('click', () => {
                    goToSlide(currentSlide + 1);
                    resetInterval();
                });
            }

            function goToSlide(index) {
                if (index < 0) {
                    index = slideCount - 1;
                } else if (index >= slideCount) {
                    index = 0;
                }

                currentSlide = index;
                updateSlider();
            }

            function updateSlider() {
                slidesContainer.style.transform = `translateX(-${currentSlide * 100}%)`;

                const dots = document.querySelectorAll('.slider-dot');
                dots.forEach((dot, index) => {
                    if (index === currentSlide) {
                        dot.classList.add('active');
                    } else {
                        dot.classList.remove('active');
                    }
                });
            }

            function startAutoSlide() {
                slideInterval = setInterval(() => {
                    goToSlide(currentSlide + 1);
                }, 5000);
            }

            function resetInterval() {
                clearInterval(slideInterval);
                startAutoSlide();
            }

            initSlider();

            // Check if user is logged in
            const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;

            // Cart popup variables
            let currentProduct = {};

            // Category Filter Functionality
            $(".category-btn").click(function () {
                const selectedCategory = $(this).data("category");

                if ($(this).hasClass("active") && selectedCategory !== "all") {
                    $(this).removeClass("active");
                    $(".category-btn[data-category='all']").addClass("active");
                    filterProducts("all");
                } else {
                    $(".category-btn").removeClass("active");
                    $(this).addClass("active");
                    filterProducts(selectedCategory);
                }
            });

            function filterProducts(category) {
                if (category === "all") {
                    $(".produk-card").show();
                } else {
                    $(".produk-card").hide();
                    $(`.produk-card[data-category="${category}"]`).show();
                }
            }

            // Login popup functions
            function showLoginPopup() {
                $('#login-popup').addClass('active').fadeIn(100);
                $('#login-popup-overlay').addClass('active').fadeIn(100);
                $('body').css('overflow', 'hidden');
                return false;
            }

            function hideLoginPopup() {
                $('#login-popup').removeClass('active').fadeOut(200);
                $('#login-popup-overlay').removeClass('active').fadeOut(200);
                $('body').css('overflow', '');
            }

            $('.login-popup-close, #login-popup-overlay').click(function () {
                hideLoginPopup();
            });

            $('.login-popup-content').click(function (e) {
                e.stopPropagation();
            });

            // Cart popup functions
            function showCartPopup(product) {
                currentProduct = product;

                $('#popup-product-image').attr('src', product.gambar);
                $('#popup-product-name').text(product.nama);
                $('#popup-product-price').text('Rp ' + formatNumber(product.harga));
                $('#popup-product-stock').text('Stock: ' + product.stok + ' items');
                $('#price-per-item').text('Rp ' + formatNumber(product.harga));

                // Reset quantity
                $('#quantity-input').val(1);
                updateTotal();

                // Set max quantity based on stock
                $('#quantity-input').attr('max', product.stok);

                $('#cart-popup').fadeIn(300);
                $('body').css('overflow', 'hidden');
            }

            function hideCartPopup() {
                $('#cart-popup').fadeOut(200);
                $('body').css('overflow', '');
            }

            function updateTotal() {
                const quantity = parseInt($('#quantity-input').val()) || 1;
                const price = currentProduct.harga;
                const total = quantity * price;

                $('#total-quantity').text(quantity);
                $('#total-price').text('Rp ' + formatNumber(total));

                // Update button states
                $('#quantity-minus').prop('disabled', quantity <= 1);
                $('#quantity-plus').prop('disabled', quantity >= currentProduct.stok);
            }

            function formatNumber(number) {
                return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            // Success popup functions
            function showSuccessPopup() {
                $('#success-popup').fadeIn(300);
                $('body').css('overflow', 'hidden');
            }

            window.hideSuccessPopup = function () {
                $('#success-popup').fadeOut(200);
                $('body').css('overflow', '');
            }

            // Cart popup event listeners
            $('#cart-popup-close, #continue-shopping').click(function () {
                hideCartPopup();
            });

            $('#cart-popup').click(function (e) {
                if (e.target === this) {
                    hideCartPopup();
                }
            });

            $('#quantity-minus').click(function () {
                const current = parseInt($('#quantity-input').val());
                if (current > 1) {
                    $('#quantity-input').val(current - 1);
                    updateTotal();
                }
            });

            $('#quantity-plus').click(function () {
                const current = parseInt($('#quantity-input').val());
                if (current < currentProduct.stok) {
                    $('#quantity-input').val(current + 1);
                    updateTotal();
                }
            });

            $('#quantity-input').on('input change', function () {
                let value = parseInt($(this).val()) || 1;
                if (value < 1) value = 1;
                if (value > currentProduct.stok) value = currentProduct.stok;
                $(this).val(value);
                updateTotal();
            });

            // Handle cart icon click
            $('.action-icon a[href="include/cart.php"]').click(function (e) {
                if (!isLoggedIn) {
                    e.preventDefault();
                    return showLoginPopup();
                }
            });

            $('.action-icon a[href="history.php"]').click(function (e) {
                if (!isLoggedIn) {
                    e.preventDefault();
                    return showLoginPopup();
                }
            });

            // Add to cart functionality
            $(".add-to-cart").click(function (e) {
                if (!isLoggedIn) {
                    e.preventDefault();
                    return showLoginPopup();
                }

                // Check if button is disabled (out of stock)
                if ($(this).prop('disabled')) {
                    return;
                }

                const product = {
                    id: $(this).data("id"),
                    nama: $(this).data("nama"),
                    harga: $(this).data("harga"),
                    gambar: $(this).data("gambar"),
                    stok: $(this).data("stok")
                };

                showCartPopup(product);
            });

            // Confirm add to cart
            $('#add-to-cart-confirm').click(function () {
                const quantity = parseInt($('#quantity-input').val());

                $.ajax({
                    url: "include/cart_action.php",
                    type: "POST",
                    data: {
                        action: "add",
                        id: currentProduct.id,
                        nama: currentProduct.nama,
                        harga: currentProduct.harga,
                        gambar: currentProduct.gambar,
                        quantity: quantity
                    },
                    success: function (response) {
                        try {
                            var result = JSON.parse(response);
                            if (result.success) {
                                updateCartCount();
                                hideCartPopup();
                                showNotification(`${quantity}x ${currentProduct.nama} telah ditambahkan ke keranjang!`);

                                // Update stock display
                                const productCard = $(`.add-to-cart[data-id="${currentProduct.id}"]`).closest('.produk-card');
                                const newStock = currentProduct.stok - quantity;

                                // Update stock info
                                let stockText = '';
                                let stockClass = '';

                                if (newStock == 0) {
                                    stockText = 'Out of Stock';
                                    stockClass = 'stock-out';
                                    productCard.find('.add-to-cart').prop('disabled', true).text('Out of Stock');
                                } else if (newStock <= 5) {
                                    stockText = `Only ${newStock} left in stock!`;
                                    stockClass = 'stock-low';
                                } else {
                                    stockText = `Stock: ${newStock} items`;
                                    stockClass = '';
                                }

                                productCard.find('.stock-info')
                                    .removeClass('stock-low stock-out')
                                    .addClass(stockClass)
                                    .text(stockText);

                                // Update data attribute
                                productCard.find('.add-to-cart').data('stok', newStock);

                            } else {
                                showNotification(result.message || "Gagal menambahkan ke keranjang");
                            }
                        } catch (e) {
                            console.error("Error parsing response:", response);
                            updateCartCount();
                            hideCartPopup();
                            showNotification(`${quantity}x ${currentProduct.nama} telah ditambahkan ke keranjang!`);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", error);
                        showNotification("Terjadi kesalahan saat menambahkan ke keranjang");
                    }
                });
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

            // Show notification
            function showNotification(message) {
                if (!document.getElementById('cart-notification')) {
                    const notification = document.createElement('div');
                    notification.id = 'cart-notification';
                    document.body.appendChild(notification);
                }

                const notification = document.getElementById('cart-notification');
                notification.textContent = message;

                // Show notification with animation
                setTimeout(() => {
                    notification.style.opacity = '1';
                    notification.style.transform = 'translateX(-50%) translateY(0)';
                }, 10);

                // Hide notification after 3 seconds
                setTimeout(() => {
                    notification.style.opacity = '0';
                    notification.style.transform = 'translateX(-50%) translateY(-20px)';
                }, 3000);
            }
        });
    </script>
</body>

</html>