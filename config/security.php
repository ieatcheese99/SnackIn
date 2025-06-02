<?php
/**
 * Enhanced Security Configuration
 * Provides comprehensive security functions for the application
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Configure secure session settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Include database connection
require_once __DIR__ . '/database.php';

/**
 * Check if user is admin
 */
if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isset($_SESSION['level']) && $_SESSION['level'] === 'admin';
    }
}

/**
 * Require admin access - redirect if not admin
 */
if (!function_exists('requireAdmin')) {
    function requireAdmin() {
        if (!isAdmin()) {
            // Log unauthorized access attempt
            logSecurityEvent('Unauthorized admin access attempt', 'User: ' . ($_SESSION['username'] ?? 'anonymous'));
            
            // Redirect to login
            header('Location: login.php?error=access_denied');
            exit;
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
    }
}

/**
 * Get current user info - SINGLE DEFINITION
 */
if (!function_exists('getCurrentUser')) {
    function getCurrentUser() {
        global $db;
        
        if (!isset($_SESSION['username'])) {
            return ['logged_in' => false];
        }
        
        // Get user data from database
        $username = $_SESSION['username'];
        $query = "SELECT * FROM user WHERE username = ?";
        $stmt = mysqli_prepare($db, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user_data = mysqli_fetch_assoc($result);
            
            if ($user_data) {
                return [
                    'id' => $user_data['id'],
                    'username' => $user_data['username'],
                    'level' => $user_data['level'],
                    'logged_in' => true
                ];
            }
        }
        
        // Fallback to session data
        return [
            'username' => $_SESSION['username'],
            'level' => $_SESSION['level'] ?? 'user',
            'logged_in' => true
        ];
    }
}

/**
 * Sanitize input data
 */
if (!function_exists('sanitize_input')) {
    function sanitize_input($data) {
        if (is_array($data)) {
            return array_map('sanitize_input', $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Validate email format
 */
if (!function_exists('validate_email')) {
    function validate_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

/**
 * Generate CSRF token
 */
if (!function_exists('generate_csrf_token')) {
    function generate_csrf_token() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

/**
 * Verify CSRF token
 */
if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

/**
 * Rate limiting check
 */
if (!function_exists('checkRateLimit')) {
    function checkRateLimit($action, $limit = 10, $window = 60) {
        $key = $action . '_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        
        if (!isset($_SESSION['rate_limit'][$key])) {
            $_SESSION['rate_limit'][$key] = [
                'count' => 1,
                'start_time' => time()
            ];
            return true;
        }
        
        $rate_data = $_SESSION['rate_limit'][$key];
        
        // Reset if window expired
        if ((time() - $rate_data['start_time']) > $window) {
            $_SESSION['rate_limit'][$key] = [
                'count' => 1,
                'start_time' => time()
            ];
            return true;
        }
        
        // Check if limit exceeded
        if ($rate_data['count'] >= $limit) {
            logSecurityEvent('Rate limit exceeded', "Action: $action, IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            return false;
        }
        
        // Increment counter
        $_SESSION['rate_limit'][$key]['count']++;
        return true;
    }
}

/**
 * Log admin actions
 */
if (!function_exists('logAdminAction')) {
    function logAdminAction($action, $details = '', $level = 'INFO') {
        $user = getCurrentUser();
        $log_entry = date('Y-m-d H:i:s') . " - $level - ADMIN ACTION - " . $action;
        
        if ($details) {
            $log_entry .= " - " . $details;
        }
        
        $log_entry .= " - User: " . ($user['username'] ?? 'anonymous');
        $log_entry .= " - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $log_entry .= PHP_EOL;
        
        // Create logs directory if it doesn't exist
        $log_dir = __DIR__ . '/../logs';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        error_log($log_entry, 3, $log_dir . '/admin.log');
    }
}

/**
 * Log security events
 */
if (!function_exists('logSecurityEvent')) {
    function logSecurityEvent($event, $details = '') {
        $log_entry = date('Y-m-d H:i:s') . " - SECURITY - " . $event;
        if ($details) {
            $log_entry .= " - " . $details;
        }
        $log_entry .= " - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $log_entry .= " - User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown');
        $log_entry .= PHP_EOL;
        
        // Create logs directory if it doesn't exist
        $log_dir = __DIR__ . '/../logs';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        error_log($log_entry, 3, $log_dir . '/security.log');
    }
}

/**
 * Prepare database statement with error handling
 */
if (!function_exists('prepare_statement')) {
    function prepare_statement($query, $params = []) {
        global $db;
        
        $stmt = mysqli_prepare($db, $query);
        if (!$stmt) {
            throw new Exception('Database prepare failed: ' . mysqli_error($db));
        }
        
        if (!empty($params)) {
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        
        return $stmt;
    }
}

/**
 * Get database connection with error handling
 */
if (!function_exists('getDatabaseConnection')) {
    function getDatabaseConnection() {
        global $db;
        
        if (!$db) {
            throw new Exception('Database connection not available');
        }
        
        return $db;
    }
}

/**
 * Validate password strength
 */
if (!function_exists('validate_password')) {
    function validate_password($password) {
        // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/', $password);
/*************  ✨ Windsurf Command ⭐  *************/
/**
 * Cleans up old session data related to rate limiting.
 *
 * This function checks for rate limit data stored in the session and removes
 * entries that are older than one hour. It is designed to ensure that outdated
 * rate limit information does not persist, helping to manage server resources
 * and maintain accurate rate limiting.
 */

/*******  50aed1d4-40b1-4ea7-9bf7-312593d1c64b  *******/    }
}

/**
 * Hash password securely
 */
if (!function_exists('hash_password')) {
    function hash_password($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}

/**
 * Verify password
 */
if (!function_exists('verify_password')) {
    function verify_password($password, $hash) {
        return password_verify($password, $hash);
    }
}

/**
 * Clean old sessions and rate limit data
 */
if (!function_exists('cleanup_sessions')) {
    function cleanup_sessions() {
        // Clean old rate limit data
        if (isset($_SESSION['rate_limit'])) {
            $current_time = time();
            foreach ($_SESSION['rate_limit'] as $key => $data) {
                if (($current_time - $data['start_time']) > 3600) { // 1 hour
                    unset($_SESSION['rate_limit'][$key]);
                }
            }
        }
    }
}

// Run cleanup periodically
if (rand(1, 100) === 1) {
    cleanup_sessions();
}

// Set security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Prevent caching of sensitive pages
if (isAdmin()) {
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
}
?>
