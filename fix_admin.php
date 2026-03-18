<?php
require_once 'config/config.php';

$username = 'admin';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

try {
    $updated = db_query("UPDATE admins SET password = ? WHERE username = ?", [$hashed_password, $username]);
    if ($updated) {
        echo "<h1>Success!</h1>";
        echo "<p>Admin password has been reset to: <strong>$password</strong></p>";
        echo "<p>Please <strong style='color:red;'>DELETE THIS FILE</strong> (fix_admin.php) immediately for security.</p>";
        echo "<a href='admin/login.php'>Go to Admin Login</a>";
    } else {
        echo "<h1>Failed!</h1>";
        echo "<p>Admin 'admin' not found in database. Make sure you have imported database.sql.</p>";
    }
} catch (Exception $e) {
    echo "<h1>Error!</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
