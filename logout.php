<?php
session_start();
session_destroy();

// Hapus cookie dengan mengatur waktunya ke masa lalu
setcookie("username", "", time() - 3600, "/");
setcookie("password", "", time() - 3600, "/");

header("Location: login.php");
exit();
?>
