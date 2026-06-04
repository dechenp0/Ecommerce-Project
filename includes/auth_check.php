<?php
// Usage at top of any protected page:
// require_once '../includes/auth_check.php'; checkAuth('user');   ← for user pages
// require_once '../includes/auth_check.php'; checkAuth('admin');  ← for admin pages
 
function checkAuth($required_role = 'user') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }
    if ($required_role === 'admin' && $_SESSION['role'] !== 'admin') {
        header("Location: ../user/dashboard.php");
        exit();
    }
    if ($required_role === 'user' && $_SESSION['role'] === 'admin') {
        header("Location: ../admin/dashboard.php");
        exit();
    }
}
?>