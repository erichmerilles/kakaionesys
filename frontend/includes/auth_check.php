<?php
// KakaiOnesys/frontend/includes/auth_check.php

// 1. Strict Session Security
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 43200,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// 2. Anti-CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 3. Ensure Database Connection
global $pdo;
if (!isset($pdo)) {
    require_once __DIR__ . '/../../config/db.php';
}

/**
 * 4. Global Auth & Auto-Redirect Logic
 * Ensures users are always where they belong.
 */
$current_page = basename($_SERVER['PHP_SELF']);

// If NOT logged in and trying to access a protected page
if (!isset($_SESSION['user_id']) && $current_page !== 'login.php') {
    header("Location: /KakaiOnesys/frontend/auth/login.php");
    exit;
}

// If ALREADY logged in and trying to access the login page
if (isset($_SESSION['user_id']) && $current_page === 'login.php') {
    header("Location: /KakaiOnesys/frontend/dashboard/admin_dashboard.php");
    exit;
}

/**
 * 5. Granular RBAC Check Function
 * Fixed for case-sensitive "Admin" check.
 */
function hasPermission($key)
{
    global $pdo;

    // Admin role bypasses all checks (Ensures capitalization matches DB)
    if (isset($_SESSION['role_name']) && $_SESSION['role_name'] === 'Admin') {
        return true;
    }

    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    try {
        $stmt = $pdo->prepare("SELECT is_granted FROM user_permissions WHERE user_id = :user_id AND permission_key = :key");
        $stmt->execute(['user_id' => $_SESSION['user_id'], 'key' => $key]);
        $perm = $stmt->fetchColumn();

        return $perm == 1;
    } catch (PDOException $e) {
        return false;
    }
}

// 6. Page Protection Enforcer
function requirePermission($key)
{
    if (!hasPermission($key)) {
        // Log unauthorized attempt or redirect to error page
        header("HTTP/1.1 403 Forbidden");
        die("Access Denied: You do not have permission to view this module.");
    }
}

// 7. CSRF Form Validation Helper
function verifyCSRF()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            die("Security Token Validation Failed. Please refresh the page and try again.");
        }
    }
}
