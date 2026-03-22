<?php
session_start();

// If admin already logged in → go dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Else → go to login page
header("Location: login.php");
exit();
