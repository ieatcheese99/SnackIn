<?php
// Database configuration for data_produk2
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
?>
