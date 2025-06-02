<?php
// Check if user is logged in
$isLoggedIn = isset($_SESSION['username']) ? true : false;

// Get cart count if user is logged in
$cartCount = 0;
if ($isLoggedIn) {
    // Use session cart for count
    $cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
}
?>

<style>
/* Enhanced Loader Component */
.loader-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #00227c 0%, #f69e22 100%);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    transition: opacity 0.5s ease-out;
}

.loader {
    width: 80px;
    height: 80px;
    position: relative;
    margin-bottom: 30px;
}

.loader::before,
.loader::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    animation: loader-pulse 2s infinite ease-in-out;
}

.loader::before {
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.3);
    animation-delay: -1s;
}

.loader::after {
    width: 60%;
    height: 60%;
    background: rgba(255, 255, 255, 0.6);
    top: 20%;
    left: 20%;
    animation-delay: -0.5s;
}

.loader-spinner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 40px;
    height: 40px;
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top: 4px solid white;
    border-radius: 50%;
    animation: loader-spin 1s linear infinite;
}

@keyframes loader-pulse {
    0%, 100% {
        transform: scale(0);
        opacity: 1;
    }
    50% {
        transform: scale(1);
        opacity: 0.3;
    }
}

@keyframes loader-spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

.loader-text {
    color: white;
    font-weight: 600;
    font-size: 24px;
    text-align: center;
    margin-bottom: 10px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.loader-subtext {
    color: rgba(255, 255, 255, 0.8);
    font-size: 16px;
    text-align: center;
    font-weight: 400;
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
    color: white;
}

.logo:hover {
    color: white;
}

.logo img {
    height: 40px;
    width: auto;
}

.main-nav ul {
    display: flex;
    gap: 30px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.main-nav a {
    font-weight: 500;
    transition: color 0.3s ease;
    position: relative;
    color: white;
    text-decoration: none;
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
    color: white;
    text-decoration: none;
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

.mobile-menu ul {
    list-style: none;
    padding: 0;
    margin: 0;
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
    text-decoration: none;
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

/* Responsive Styles */
@media (max-width: 992px) {
    .main-nav {
        display: none;
    }

    .mobile-menu-toggle {
        display: block;
    }
}

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
}
</style>

<!-- Enhanced Loader -->
<div class="loader-overlay" id="loader">
    <div class="loader">
        <div class="loader-spinner"></div>
    </div>
    <div class="loader-text">SNACK IN</div>
    <div class="loader-subtext">Loading your favorite snacks...</div>
</div>

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
                    <span class="badge cart-count"><?php echo $cartCount; ?></span>
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
        <li><a href="user_ui.php#produk">Shop</a></li>
        <li><a href="user_ui.php#testimonials">Testimoni</a></li>
        <li><a href="user_ui.php#footer">Contact</a></li>
    </ul>
</div>
<div class="mobile-menu-overlay" id="mobile-menu-overlay"></div>

<script>
// Hide loader after page loads
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const loader = document.getElementById('loader');
        if (loader) {
            loader.style.opacity = '0';
            setTimeout(function() {
                loader.style.display = 'none';
            }, 500);
        }
    }, 1500);

    // Mobile menu functionality
    const mobileMenuToggle = document.getElementById("mobile-menu-toggle");
    const mobileMenu = document.getElementById("mobile-menu");
    const mobileMenuClose = document.getElementById("mobile-menu-close");
    const mobileMenuOverlay = document.getElementById("mobile-menu-overlay");

    // Toggle mobile menu
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener("click", () => {
            if (mobileMenu) {
                mobileMenu.classList.add('active');
                if (mobileMenuOverlay) {
                    mobileMenuOverlay.classList.add('active');
                }
                document.body.style.overflow = "hidden";
            }
        });
    }

    // Close mobile menu
    function closeMobileMenu() {
        if (mobileMenu) {
            mobileMenu.classList.remove('active');
            if (mobileMenuOverlay) {
                mobileMenuOverlay.classList.remove('active');
            }
            document.body.style.overflow = "";
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
