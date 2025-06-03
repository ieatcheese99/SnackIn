<?php
// Database configuration
$host = "localhost";
$username = "root"; 
$password = "";
$database = "data_produk2";

// Create connection
$db = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($db, "utf8");

// Helper functions - only define if not already defined
if (!function_exists('execute_query')) {
    function execute_query($query) {
        global $db;
        $result = mysqli_query($db, $query);
        
        if (!$result) {
            error_log("Database Error: " . mysqli_error($db));
            return false;
        }
        
        return $result;
    }
}

if (!function_exists('escape_string')) {
    function escape_string($string) {
        global $db;
        return mysqli_real_escape_string($db, $string);
    }
}

if (!function_exists('sanitize_input')) {
    function sanitize_input($data) {
        if (is_array($data)) {
            return array_map('sanitize_input', $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('logAdminAction')) {
    function logAdminAction($action, $details = '', $level = 'INFO') {
        $user = $_SESSION['username'] ?? 'anonymous';
        $log_entry = date('Y-m-d H:i:s') . " - $level - ADMIN ACTION - " . $action;
        
        if ($details) {
            $log_entry .= " - " . $details;
        }
        
        $log_entry .= " - User: " . $user;
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
?>
