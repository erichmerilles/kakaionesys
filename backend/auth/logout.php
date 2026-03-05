<?php
// KakaiOnesys/backend/auth/logout.php

// 1. Initialize session to access it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Log the logout action before destroying the session
// This maintains a professional audit trail for the panel
if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/../../config/db.php';

    try {
        $stmtLog = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description, ip_address) VALUES (?, 'LOGOUT', ?, ?)");
        $stmtLog->execute([
            $_SESSION['user_id'],
            "User logged out manually",
            $_SERVER['REMOTE_ADDR']
        ]);
    } catch (Exception $e) {
        // Silently continue if logging fails to ensure logout proceeds
    }
}

// 3. Unset all session variables
$_SESSION = array();

// 4. If using cookies for sessions (standard PHP), destroy the cookie
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

// 5. Finally, destroy the session on the server
session_destroy();

// 6. Redirect back to the login page
header("Location: /KakaiOnesys/frontend/auth/login.php");
exit;
