<?php
session_start();

// Clear all session variables
session_unset();

// Destroy the session
session_destroy();

// Clear remember me cookies if they exist
if (isset($_COOKIE['username'])) {
    setcookie("username", "", time() - 3600, "/");
}
if (isset($_COOKIE['password'])) {
    setcookie("password", "", time() - 3600, "/");
}
if (isset($_COOKIE['user_login'])) {
    setcookie("user_login", "", time() - 3600, "/");
}
if (isset($_COOKIE['remember_token'])) {
    setcookie("remember_token", "", time() - 3600, "/");
}

// Redirect to login page
header("Location: login.php");
exit();
?>
    