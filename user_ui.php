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
    <title>Snack In - Your Favorite Snack Store</title>
    <style>
        /* Reset and Base Styles */
        :root {
            --primary-color: #00227c;
            --secondary-color: #00227c;
            --accent-color: #f48c06;
            --dark-blue: #00227c;
            --white: #ffffff;
            --orange: #f69e22;
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
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f69e22;
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
            margin-bottom: 40px;
        }

        .section-title h2 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .section-title .divider {
            width: 80px;
            height: 4px;
            background-color: #f69e22;
            margin: 0 auto;
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            height: 600px;
            overflow: hidden;
            background-color: var(--dark-blue);
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
            transition: transform 0.5s ease-in-out;
        }

        .hero-slide {
            position: relative;
            min-width: 100%;
            width: 100%;
            height: 100%;
            color: var(--white);
            display: flex;
            align-items: center;
            padding: 0 5%;
            overflow: hidden;
        }

        .hero-content {
            width: 50%;
            position: relative;
            z-index: 2;
            padding: 20px;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            text-transform: uppercase;
            color: var(--white);
            font-weight: 800;
            line-height: 1.2;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            max-width: 600px;
            color: var(--white);
            line-height: 1.6;
            text-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
        }

        .hero-btn {
            display: inline-flex;
            align-items: center;
            padding: 14px 28px;
            background-color: var(--accent-color);
            color: var(--white);
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .hero-btn .arrow-icon {
            margin-left: 10px;
            font-style: normal;
            transition: transform 0.3s ease;
        }

        .hero-btn:hover {
            background-color: #e67e00;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }

        .hero-btn:hover .arrow-icon {
            transform: translateX(5px);
        }

        .hero-image {
            position: absolute;
            right: 5%;
            width: 50%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .hero-image img {
            max-width: 100%;
            max-height: 90%;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.2));
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-15px);
            }

            100% {
                transform: translateY(0px);
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
        .daftar-produk {
            margin-top: 50px;
            margin-bottom: 50px;
        }

        .daftar-produk h2 {
            text-align: center;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: #00227c;
        }

        /* Category Filter */
        .category-filter {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .category-btn {
            padding: 8px 20px;
            background-color: #f5f5f5;
            border: 2px solid #ddd;
            border-radius: 30px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .category-btn:hover {
            background-color: #e9e9e9;
        }

        .category-btn.active {
            background-color: #00227c;
            color: white;
            border-color: #00227c;
        }

        .produk-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .produk-card {
            border: 2px solid #ddd;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            background: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .produk-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .produk-card .image-container {
            position: relative;
            margin-bottom: 15px;
        }

        .produk-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }

        .produk-card h3 {
            margin: 15px 0 10px 0;
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }

        .produk-card .price {
            color: #00227c;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .produk-card .description {
            color: #666;
            margin-bottom: 15px;
            flex-grow: 1;
            line-height: 1.5;
        }

        .category-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(246, 158, 34, 0.9);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            z-index: 2;
        }

        .stock-info {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
            padding: 8px;
            background-color: #f8f9fa;
            border-radius: 6px;
        }

        .stock-low {
            color: #ff6b6b;
            font-weight: bold;
            background-color: #fff5f5;
        }

        .stock-out {
            color: #dc3545;
            font-weight: bold;
            background-color: #f8d7da;
        }

        /* Cart Popup Styles */
        .cart-popup {
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

        .cart-popup-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 16px;
            padding: 0;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: popupSlideIn 0.3s ease-out;
        }

        @keyframes popupSlideIn {
            from {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.8);
            }

            to {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }

        .cart-popup-header {
            background: linear-gradient(135deg, #00227c, #0a3baf);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-popup-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .cart-popup-close {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.3s;
        }

        .cart-popup-close:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .cart-popup-body {
            padding: 25px;
        }

        .product-info {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .product-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid #f0f0f0;
        }

        .product-details h4 {
            margin: 0 0 8px 0;
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }

        .product-price {
            font-size: 18px;
            font-weight: 700;
            color: #00227c;
            margin-bottom: 8px;
        }

        .product-stock {
            font-size: 12px;
            color: #666;
        }

        .quantity-section {
            margin-bottom: 25px;
        }

        .quantity-section label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #333;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 15px;
            justify-content: center;
        }

        .quantity-btn {
            width: 40px;
            height: 40px;
            border: 2px solid #00227c;
            background: white;
            color: #00227c;
            border-radius: 50%;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover:not(:disabled) {
            background: #00227c;
            color: white;
            transform: scale(1.1);
        }

        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .quantity-input {
            width: 60px;
            height: 40px;
            text-align: center;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
        }

        .total-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .total-row:last-child {
            margin-bottom: 0;
            padding-top: 10px;
            border-top: 2px solid #ddd;
            font-weight: 700;
            font-size: 18px;
        }

        .cart-popup-actions {
            display: flex;
            gap: 12px;
        }

        .btn-secondary {
            flex: 1;
            padding: 12px;
            border: 2px solid #ddd;
            background: white;
            color: #666;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            border-color: #bbb;
            color: #333;
        }

        .btn-primary {
            flex: 2;
            padding: 12px;
            background: linear-gradient(135deg, #00227c, #0a3baf);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 34, 124, 0.3);
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
                height: 500px;
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
                height: 450px;
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
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
                    <img src="/placeholder.svg?height=80&width=80" alt="" class="product-image" id="popup-product-image">
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
            <p class="success-message">Your order has been placed successfully. You will receive a confirmation shortly.</p>
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
                        <p>Jelajahi berbagai produk makanan ringan berkualitas tinggi dari Snack In. Nikmati sensasi rasa yang tak terlupakan dan kualitas terbaik.</p>
                        <a href="#produk" class="hero-btn">
                            <span>Lihat Produk</span>
                            <i class="arrow-icon">→</i>
                        </a>
                    </div>
                    <div class="hero-image">
                        <img src="/placeholder.svg?height=400&width=400" alt="Hero Image 1">
                    </div>
                </div>
                <div class="hero-slide" style="background: linear-gradient(135deg, #00227c 0%, #0a3baf 100%);">
                    <div class="hero-content">
                        <h1>CEMILAN ASLI INDONESIA</h1>
                        <p>Diolah dengan resep tradisional yang dipadukan dengan teknologi modern. Rasakan kelezatan asli Indonesia dalam setiap gigitannya.</p>
                        <a href="#produk" class="hero-btn">
                            <span>Tentang Kami</span>
                            <i class="arrow-icon">→</i>
                        </a>
                    </div>
                    <div class="hero-image">
                        <img src="/placeholder.svg?height=400&width=400" alt="Hero Image 2">
                    </div>
                </div>
                <div class="hero-slide" style="background: linear-gradient(135deg, #00227c 0%, #0a3baf 100%);">
                    <div class="hero-content">
                        <h1>KUALITAS TERBAIK</h1>
                        <p>Kami hanya menggunakan bahan-bahan berkualitas terbaik untuk menghasilkan produk yang lezat dan sehat untuk dinikmati bersama keluarga.</p>
                        <a href="#produk" class="hero-btn">
                            <span>Belanja Sekarang</span>
                            <i class="arrow-icon">→</i>
                        </a>
                    </div>
                    <div class="hero-image">
                        <img src="/placeholder.svg?height=400&width=400" alt="Hero Image 3">
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

    <section class="container daftar-produk" id="produk">
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
                        <img src="<?php echo htmlspecialchars($row['gambar']); ?>" alt="<?php echo htmlspecialchars($row['nama']); ?>" class="produk-img">
                        <span class="category-badge"><?php echo htmlspecialchars($row['kategori']); ?></span>
                    </div>
                    <h3><?php echo htmlspecialchars($row['nama']); ?></h3>
                    <div class="price">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></div>
                    <p class="description"><?php echo htmlspecialchars(substr($row['deskripsi'], 0, 80)); ?>...</p>
                    <div class="stock-info <?php echo $row['Stok'] <= 5 ? ($row['Stok'] == 0 ? 'stock-out' : 'stock-low') : ''; ?>">
                        <?php if ($row['Stok'] == 0): ?>
                            Out of Stock
                        <?php elseif ($row['Stok'] <= 5): ?>
                            Only <?php echo $row['Stok']; ?> left in stock!
                        <?php else: ?>
                            Stock: <?php echo $row['Stok']; ?> items
                        <?php endif; ?>
                    </div>
                    <button class="btn add-to-cart"
                        data-id="<?php echo $row['id']; ?>"
                        data-nama="<?php echo htmlspecialchars($row['nama']); ?>"
                        data-harga="<?php echo $row['harga']; ?>"
                        data-gambar="<?php echo htmlspecialchars($row['gambar']); ?>"
                        data-stok="<?php echo $row['Stok']; ?>"
                        <?php echo $row['Stok'] == 0 ? 'disabled' : ''; ?>>
                        <?php echo $row['Stok'] == 0 ? 'Out of Stock' : 'Tambah ke Keranjang'; ?>
                    </button>
                </div>
            <?php } ?>
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
                    <p class="testimonial-text">"Snack In adalah tempat favorit saya untuk membeli cemilan. Thai Tea mereka sangat enak dan menyegarkan!"</p>
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
                    <p class="testimonial-text">"Saya selalu membeli Capuccino dari Snack In setiap kali berkunjung. Rasanya sangat Enak dan harganya terjangkau!"</p>
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
                    <p class="testimonial-text">"Basreng dari Snack In adalah yang terbaik! Renyah, pedas, dan sangat cocok untuk camilan saat nonton film."</p>
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
                        <li><a href="about.php">About</a></li>
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
        $(document).ready(function() {
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
            $(".category-btn").click(function() {
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

            $('.login-popup-close, #login-popup-overlay').click(function() {
                hideLoginPopup();
            });

            $('.login-popup-content').click(function(e) {
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

            window.hideSuccessPopup = function() {
                $('#success-popup').fadeOut(200);
                $('body').css('overflow', '');
            }

            // Cart popup event listeners
            $('#cart-popup-close, #continue-shopping').click(function() {
                hideCartPopup();
            });

            $('#cart-popup').click(function(e) {
                if (e.target === this) {
                    hideCartPopup();
                }
            });

            $('#quantity-minus').click(function() {
                const current = parseInt($('#quantity-input').val());
                if (current > 1) {
                    $('#quantity-input').val(current - 1);
                    updateTotal();
                }
            });

            $('#quantity-plus').click(function() {
                const current = parseInt($('#quantity-input').val());
                if (current < currentProduct.stok) {
                    $('#quantity-input').val(current + 1);
                    updateTotal();
                }
            });

            $('#quantity-input').on('input change', function() {
                let value = parseInt($(this).val()) || 1;
                if (value < 1) value = 1;
                if (value > currentProduct.stok) value = currentProduct.stok;
                $(this).val(value);
                updateTotal();
            });

            // Handle cart icon click
            $('.action-icon a[href="include/cart.php"]').click(function(e) {
                if (!isLoggedIn) {
                    e.preventDefault();
                    return showLoginPopup();
                }
            });

            $('.action-icon a[href="history.php"]').click(function(e) {
                if (!isLoggedIn) {
                    e.preventDefault();
                    return showLoginPopup();
                }
            });

            // Add to cart functionality
            $(".add-to-cart").click(function(e) {
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
            $('#add-to-cart-confirm').click(function() {
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
                    success: function(response) {
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
                    error: function(xhr, status, error) {
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
                    success: function(count) {
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