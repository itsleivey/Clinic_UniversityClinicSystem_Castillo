<?php
session_start();
$_SESSION = [];                 // Clear all session variables
session_unset();
session_destroy();              // Destroy the session on the server

// Remove session cookie from browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Redirect to login
header("Location: index.php");
exit();
