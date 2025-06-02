<?php
// Enhanced Admin Check with Security Improvements
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include security configuration
require_once __DIR__ . '/security.php';

// This file is now just a wrapper for the security functions
// All the actual security logic is in config/security.php

// For backward compatibility, we'll keep the old function names
if (!function_exists('requireAdmin')) {
    function requireAdmin() {
        return \requireAdmin();
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin() {
        return \isAdmin();
    }
}

if (!function_exists('getCurrentUser')) {
    function getCurrentUser() {
        return \getCurrentUser();
    }
}

if (!function_exists('logAdminAction')) {
    function logAdminAction($action, $details = '', $level = 'INFO') {
        return \logAdminAction($action, $details, $level);
    }
}

// Additional security checks
function checkAdminSession() {
    // Check if session is valid
    if (!isset($_SESSION['username']) || !isset($_SESSION['level'])) {
        return false;
    }
    
    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        session_unset();
        session_destroy();
        return false;
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    
    return $_SESSION['level'] === 'admin';
}

// Enhanced logging with more details
function logSecurityEvent($event, $details = '') {
    $log_entry = date('Y-m-d H:i:s') . " - SECURITY - " . $event;
    if ($details) {
        $log_entry .= " - " . $details;
    }
    $log_entry .= " - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $log_entry .= " - User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown');
    $log_entry .= PHP_EOL;
    
    // Create logs directory if it doesn't exist
    $log_dir = __DIR__ . '/logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    error_log($log_entry, 3, $log_dir . '/security.log');
}

// Check for suspicious activity
function checkSuspiciousActivity() {
    // Check for rapid requests
    if (!isset($_SESSION['request_count'])) {
        $_SESSION['request_count'] = 0;
        $_SESSION['request_start_time'] = time();
    }
    
    $_SESSION['request_count']++;
    
    // If more than 100 requests in 60 seconds, log as suspicious
    if ($_SESSION['request_count'] > 100 && (time() - $_SESSION['request_start_time']) < 60) {
        logSecurityEvent('Suspicious Activity', 'High request rate detected');
        return false;
    }
    
    // Reset counter every minute
    if ((time() - $_SESSION['request_start_time']) > 60) {
        $_SESSION['request_count'] = 1;
        $_SESSION['request_start_time'] = time();
    }
    
    return true;
}

// Initialize security checks
checkSuspiciousActivity();
?>
