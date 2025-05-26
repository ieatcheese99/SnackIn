<?php
require "config/database.php";

// Check if user is logged in
session_start();
$isLoggedIn = isset($_SESSION['username']) ? true : false;

// Fetch all products with their category information
$queryProduk = mysqli_query($db, "SELECT b.id, b.nama, b.harga, b.gambar, b.deskripsi, k.nama as kategori 
                                 FROM barang b 
                                 JOIN kategori k ON b.kategori_id = k.id 
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

        
    
        /* Featured Banner */
        .snack-section {
            position: relative;
            width: 100%;
            background: linear-gradient(135deg, #f69e22 0%, #ff7e1a 100%);
            color: white;
            padding: 60px 20px;
            overflow: hidden;
        }
        
        .snack-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 10;
        }
        
        .snack-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .featured-logo {
            max-width: 300px;
            margin-bottom: 30px;
            animation: float 3s ease-in-out infinite;
        }
        
        .snack-tagline {
            font-size: 36px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .snack-products {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px;
            margin: 20px 0 50px;
        }
        
        .product-item {
            position: relative;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }
        
        .product-item:hover {
            transform: scale(1.05);
        }
        
        .product-border {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 4px solid rgba(255,255,255,0.5);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 0.8;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.5;
            }
            100% {
                transform: scale(1);
                opacity: 0.8;
            }
        }
        
        .product-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .ambassador-section {
            text-align: center;
            margin: 40px 0;
        }
        
        .ambassador-heading {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 30px;
            position: relative;
            display: inline-block;
        }
        
        .ambassador-heading:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: white;
        }
        
        .ambassador-grid {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 40px;
            margin-top: 20px;
        }
        
        .ambassador-card {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .ambassador-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            margin-bottom: 15px;
        }
        
        .ambassador-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .ambassador-name {
            font-weight: bold;
            font-size: 18px;
            margin-top: 10px;
        }
        
        .cta-section {
            margin-top: 40px;
            text-align: center;
        }
        
        .order-button {
            display: inline-block;
            padding: 15px 40px;
            background-color:#00277c;
            color: #f69e22;
            font-size: 18px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }
        
        .order-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            background-color: #fff8e1;
        }
        
        .contact-info {
            text-align: center;
            margin-top: 30px;
        }
        
        .contact-text {
            margin: 5px 0;
            font-size: 16px;
            font-weight: bold;
        }
        
        .location-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
        }
        
        .location-icon {
            font-size: 18px;
        }
        
        .decoration-circles {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 1;
            overflow: hidden;
        }
        
        .decoration-circle {
            position: absolute;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .circle1 {
            width: 300px;
            height: 300px;
            top: -100px;
            left: -100px;
        }
        
        .circle2 {
            width: 200px;
            height: 200px;
            bottom: -50px;
            right: 10%;
        }
        
        .circle3 {
            width: 150px;
            height: 150px;
            top: 30%;
            right: -50px;
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            .snack-tagline {
                font-size: 28px;
            }
            
            .snack-products {
                gap: 20px;
            }
            
            .product-item {
                width: 120px;
                height: 120px;
            }
            
            .ambassador-grid {
                gap: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .snack-tagline {
                font-size: 24px;
            }
            
            .product-item {
                width: 100px;
                height: 100px;
            }
            
            .ambassador-photo {
                width: 100px;
                height: 100px;
            }
            
            .ambassador-name {
                font-size: 16px;
            }
            
            .order-button {
                padding: 12px 30px;
                font-size: 16px;
            }
        }
        /* Hero Section */
        /* Enhanced Hero Section */
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
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            max-width: 600px;
            color: var(--white);
            line-height: 1.6;
            text-shadow: 0 1px 5px rgba(0,0,0,0.1);
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
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .hero-btn .arrow-icon {
            margin-left: 10px;
            font-style: normal;
            transition: transform 0.3s ease;
        }

        .hero-btn:hover {
            background-color: #e67e00;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.25);
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
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.2));
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

        /* Responsive Hero Section */
        @media screen and (max-width: 1200px) {
            .hero-content h1 {
                font-size: 3rem;
            }
            
            .hero-content p {
                font-size: 1.1rem;
            }
        }

        @media screen and (max-width: 992px) {
            .hero-section {
                height: 500px;
            }
            
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .hero-content p {
                font-size: 1rem;
            }
            
            .hero-btn {
                padding: 12px 24px;
            }
        }

        @media screen and (max-width: 768px) {
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
        }

        @media screen and (max-width: 480px) {
            .hero-section {
                height: 400px;
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
        }
        /* Brand Ambassador Styles */
        .ambassador-heading {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin: 40px 0 20px;
            color: white;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
        }

        .ambassador-circles {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px;
            margin: 0 0 40px;
        }

        .ambassador-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.3s ease;
        }

        .ambassador-item:hover {
            transform: translateY(-5px);
        }

        .ambassador-circle {
            position: relative;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            border: 3px solid rgba(255,255,255,0.8);
        }

        .ambassador-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .ambassador-name {
            margin-top: 10px;
            font-weight: 600;
            color: white;
            font-size: 14px;
            text-align: center;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }
        @media screen and (max-width: 768px) {
        
        .ambassador-circle {
            width: 100px;
            height: 100px;
        }
        
        .ambassador-circles {
            gap: 20px;
        }
        
        .ambassador-heading {
            font-size: 22px;
            margin: 30px 0 15px;
        }
    }
    @media screen and (max-width: 480px) {
        .ambassador-circle {
            width: 80px;
            height: 80px;
        }
        
        .ambassador-circles {
            gap: 15px;
        }
        
        .ambassador-name {
            font-size: 12px;
        }
        
        .ambassador-heading {
            font-size: 20px;
            margin: 25px 0 15px;
        }
    }


        /* Different text positioning classes */
        .text-left {
            align-self: flex-start;
            text-align: left;
            order: 2;
        }

        .text-right {
            align-self: flex-end;
            text-align: right;
            order: 1;
            margin-bottom: 1rem;
        }

        .text-center {
            align-self: center;
            text-align: center;
            order: 1;
        }

        .text-bottom {
            align-self: flex-end;
            text-align: center;
            order: 2;
            margin-top: 1rem;
        }

        .text-bottom-left {
            align-self: flex-start;
            text-align: left;
            order: 2;
            margin-top: 1rem;
        }

        .text-top-right {
            align-self: flex-end;
            text-align: right;
            order: 1;
            margin-bottom: 1rem;
        }

        .slide-image-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            max-width: 400px;
        }

        .slide-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .slide-description {
            font-size: 1.25rem;
            max-width: 100%;
        }

        .slide-image {
            max-width: 100%;
            height: auto;
            object-fit: contain;
            border-radius: 12px;
        }

       /* Testimonials Section */
       .testimonials {
            padding: 80px 0;
            background-color: #00227c;
            color: white;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
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
        }

        .testimonial-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            justify-content: center;
            max-width: 1100px;
            margin: 0 auto;
        }

        .produk-card {
            width: 250px;
            height: 100%;
            border: 2px solid #ddd;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            background: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease-in-out;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: calc(33.33% - 16px);
            max-width: 250px;
        }

        .produk-card:hover {
            transform: scale(1.05);
        }

        .produk-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }

        .produk-card h3 {
            margin: 10px 0;
        }

        .produk-card p {
            flex-grow: 1;
            margin-bottom: 10px;
        }

        .produk-card button {
            margin-top: auto;
        }

        .category-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(246, 158, 34, 0.8);
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
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
            .main-nav {
                display: none;
            }

            .mobile-menu-toggle {
                display: block;
            }
            .banner-slider {
                height: 500px;
            }

            .slide-title {
                font-size: 28px;
            }

            .slide-description {
                font-size: 16px;
            }

            .slide-image {
                max-width: 200px;
            }

            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .testimonials-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .produk-card {
                width: calc(50% - 16px);
            }
        }


        @media (max-width: 768px) {
            

            .testimonials-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .produk-card {
                width: calc(50% - 16px);
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
         

            .produk-card {
                width: 100%;
                max-width: 300px;
            }
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

        .btn-login, .btn-register {
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
    </style>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
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
                    <li><a href="#produk">Shop</a></li>
                    <li><a href="#testimonials">Testimoni</a></li>
                    <li><a href="#footer">Contact</a></li>
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
                <?php if($isLoggedIn): ?>
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
            <li><a href="#produk">Shop</a></li>
            <li><a href="#testimonials">Testimoni</a></li>
            <li><a href="#footer">Contact</a></li>
        </ul>
    </div>
    <div class="mobile-menu-overlay" id="mobile-menu-overlay"></div>

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
                        <img src="assets/img/pngtree-creamy-oreo-milkshake-png-image_13066217.png" alt="Hero Image 1">
                    </div>
                </div>
                <div class="hero-slide" style="background: linear-gradient(135deg, #00227c 0%, #0a3baf 100%);">
                    <div class="hero-content">
                        <h1>CEMILAN ASLI INDONESIA</h1>
                        <p>Diolah dengan resep tradisional yang dipadukan dengan teknologi modern. Rasakan kelezatan asli Indonesia dalam setiap gigitannya.</p>
                        <a href="#tentang" class="hero-btn">
                            <span>Tentang Kami</span>
                            <i class="arrow-icon">→</i>
                        </a>
                    </div>
                    <div class="hero-image">
                        <img src="assets\img\basreng-bakso-goreng-snack-sliced-600nw-2443716985.webp" alt="Hero Image 2">
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
                        <img src="images\downloadsingkong.jpg" alt="Hero Image 3">
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
            <?php while ($kategori = mysqli_fetch_assoc($queryKategori)) { ?>
                <button class="category-btn" data-category="<?php echo $kategori['nama']; ?>"><?php echo $kategori['nama']; ?></button>
            <?php } ?>
        </div>

        <div class="produk-container" id="produk-container">
            <!-- Products will be loaded dynamically from PHP -->
            <?php while ($row = mysqli_fetch_assoc($queryProduk)) { ?>
                <div class="produk-card" data-category="<?php echo $row['kategori']; ?>">
                    <div style="position: relative;">
                        <img src="<?php echo $row['gambar']; ?>" alt="<?php echo $row['nama']; ?>" class="produk-img">
                        <span class="category-badge"><?php echo $row['kategori']; ?></span>
                    </div>
                    <h3><?php echo $row['nama']; ?></h3>
                    <p style="color: green;">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></p>
                    <p><?php echo substr($row['deskripsi'], 0, 50); ?>...</p>
                    <button class="btn btn-success add-to-cart"
                        data-id="<?php echo $row['id']; ?>"
                        data-nama="<?php echo $row['nama']; ?>"
                        data-harga="<?php echo $row['harga']; ?>"
                        data-gambar="<?php echo $row['gambar']; ?>">
                        Tambah ke Keranjang
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
                        <img src="4.png" alt="4.png">
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
                        <img src="3.png" alt="3.png">
                    </div>
                    <div class="testimonial-stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"Saya selalu membeli Kue soes  dari Snack In setiap kali berkunjung. Rasanya sangat Enak dan harganya terjangkau!"</p>
                    <h3 class="testimonial-name">Naila Aribah</h3>
                </div>

                <!-- Testimonial 3 -->
                <div class="testimonial-item">
                    <div class="testimonial-image">
                        <img src="2.png" alt="2.png">
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
                        <li><a href="index.html">Home</a></li>
                        <li><a href="about.html">Testimoni</a></li>
                        <li><a href="shop.html">Shop</a></li>
                        <li><a href="contact.html">Contact</a></li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div class="footer-column footer-newsletter">
                    <h3>Newsletter</h3>
                    <p>Subscribe to our newsletter to get the latest updates and offers.</p>
                    <div class="newsletter-form">
                        <input type="email" placeholder="Your email address">
                        <button type="submit" class="btn">Subscribe</button>
                    </div>
                    <div class="footer-social">
                        <h4>Follow Us</h4>
                        <div class="social-icons">
                            <a href="https://instagram.com" target="_blank"><i class="fab fa-instagram"></i></a>
                            <a href="tel:+6281211734491" target="_blank"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>
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
                // Set initial position
                updateSlider();
                
                // Start auto-sliding
                startAutoSlide();
                
                // Add event listeners for navigation
                prevBtn.addEventListener('click', () => {
                    goToSlide(currentSlide - 1);
                    resetInterval();
                });
                
                nextBtn.addEventListener('click', () => {
                    goToSlide(currentSlide + 1);
                    resetInterval();
                });
                
                // Add touch events for mobile
                let startX, endX;
                const slider = document.getElementById('hero-slider');
                
                slider.addEventListener('touchstart', (e) => {
                    startX = e.touches[0].clientX;
                }, { passive: true });
                
                slider.addEventListener('touchend', (e) => {
                    endX = e.changedTouches[0].clientX;
                    handleSwipe();
                }, { passive: true });
                
                function handleSwipe() {
                    const threshold = 50; // Minimum distance for swipe
                    if (startX - endX > threshold) {
                        // Swipe left
                        goToSlide(currentSlide + 1);
                        resetInterval();
                    } else if (endX - startX > threshold) {
                        // Swipe right
                        goToSlide(currentSlide - 1);
                        resetInterval();
                    }
                }
            }
            
            // Go to specific slide
            function goToSlide(index) {
                // Handle wrapping
                if (index < 0) {
                    index = slideCount - 1;
                } else if (index >= slideCount) {
                    index = 0;
                }
                
                currentSlide = index;
                updateSlider();
            }
            
            // Update slider position and active dot
            function updateSlider() {
                // Update slides position
                slidesContainer.style.transform = `translateX(-${currentSlide * 100}%)`;
                
                // Update navigation dots
                const dots = document.querySelectorAll('.slider-dot');
                dots.forEach((dot, index) => {
                    if (index === currentSlide) {
                        dot.classList.add('active');
                    } else {
                        dot.classList.remove('active');
                    }
                });
            }
            
            // Start auto-sliding
            function startAutoSlide() {
                slideInterval = setInterval(() => {
                    goToSlide(currentSlide + 1);
                }, 5000); // Change slide every 5 seconds
            }
            
            // Reset interval for auto-sliding
            function resetInterval() {
                clearInterval(slideInterval);
                startAutoSlide();
            }
            
            // Initialize the slider
            initSlider();
            
            // Check if user is logged in
            const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
            
            // Category Filter Functionality
            $(".category-btn").click(function() {
                const selectedCategory = $(this).data("category");

                // Toggle active class
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

            // Filter products based on category
            function filterProducts(category) {
                if (category === "all") {
                    $(".produk-card").show();
                } else {
                    $(".produk-card").hide();
                    $(`.produk-card[data-category="${category}"]`).show();
                }
            }
            
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
            $('.login-popup-close, #login-popup-overlay').click(function() {
                hideLoginPopup();
            });
            
            // Prevent propagation when clicking on popup content to avoid closing
            $('.login-popup-content').click(function(e) {
                e.stopPropagation();
            });
            
            // Handle cart icon click
            $('.action-icon a[href="include/cart.php"]').click(function(e) {
                if (!isLoggedIn) {
                    e.preventDefault();
                    return showLoginPopup();
                }
            });

            // Handle history link click
            $('.action-icon a[href="history.php"]').click(function(e) {
                if (!isLoggedIn) {
                    e.preventDefault();
                    return showLoginPopup();
                }
            });

            // Add to cart functionality using AJAX
            $(".add-to-cart").click(function(e) {
                // If not logged in, show login popup instead
                if (!isLoggedIn) {
                    e.preventDefault();
                    return showLoginPopup();
                }
                
                var id = $(this).data("id");
                var nama = $(this).data("nama");
                var harga = $(this).data("harga");
                var gambar = $(this).data("gambar");

                $.ajax({
                    url: "include/cart_action.php", // Correct path to the cart action file
                    type: "POST",
                    data: {
                        action: "add",
                        id: id,
                        nama: nama,
                        harga: harga,
                        gambar: gambar
                    },
                    success: function(response) {
                        try {
                            var result = JSON.parse(response);
                            if(result.success) {
                                updateCartCount();
                                showNotification(`${nama} telah ditambahkan ke keranjang!`);
                            } else {
                                showNotification("Gagal menambahkan ke keranjang");
                            }
                        } catch(e) {
                            console.error("Error parsing response:", response);
                            updateCartCount();
                            showNotification(`${nama} telah ditambahkan ke keranjang!`);
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
                    url: "include/cart_action.php", // Correct path to the cart action file
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
                // Create notification element if it doesn't exist
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
    </script>
</body>

</html>
