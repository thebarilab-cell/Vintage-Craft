<?php
/**
 * ============================================
 * USER LOGOUT SCRIPT
 * ============================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear user session variables
unset($_SESSION['user_id']);
unset($_SESSION['user_name']);
unset($_SESSION['user_email']);
unset($_SESSION['user_first_name']);
unset($_SESSION['user_last_name']);

// If there's a redirect after login, clear it
unset($_SESSION['redirect_after_login']);

// Redirect to login page with a message
header('Location: login.php?logout=success');
exit;
?>
