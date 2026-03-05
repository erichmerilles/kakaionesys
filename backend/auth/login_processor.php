<?php
// KakaiOnesys/backend/auth/login_processor.php

// 1. Strict Session Security (Initialized before anything else)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 43200, // 12 hours
        'path' => '/',
        'secure' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

require_once __DIR__ . '/../../config/db.php';

// 2. Ensure no HTML warnings corrupt the JSON response
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

try {
    // 3. Centralized validation using Exceptions
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        throw new Exception("Please fill in all fields.");
    }

    global $pdo;

    // --- TEMPORARY AUTO-FIX: FORCIBLY RESET THE ADMIN PASSWORD ---
    // This generates a real, valid BCRYPT hash for 'admin123' and updates the DB instantly.
    if ($username === 'admin') {
        $realHash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->query("UPDATE users SET password_hash = '$realHash' WHERE username = 'admin'");
    }
    // -------------------------------------------------------------

    // 4. Securely fetch user data (using LEFT JOIN for debugging)
    $stmt = $pdo->prepare("
        SELECT u.user_id, u.username, u.password_hash, u.full_name, u.is_active, u.role_id, r.role_name 
        FROM users u 
        LEFT JOIN roles r ON u.role_id = r.role_id 
        WHERE u.username = ? LIMIT 1
    ");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // DEBUG CHECK 1: Does the user exist at all?
    if (!$user) {
        throw new Exception("DEBUG: The username '$username' does not exist in the users table.");
    }

    // DEBUG CHECK 2: Does the password match the hash?
    if (!password_verify($password, $user['password_hash'])) {
        throw new Exception("DEBUG: Username found, but the password does not match the stored hash. (Hash in DB: " . substr($user['password_hash'], 0, 10) . "...)");
    }

    // DEBUG CHECK 3: Is the role missing?
    if (empty($user['role_name'])) {
        throw new Exception("DEBUG: Password is correct, but role_id '" . $user['role_id'] . "' does not exist in the roles table.");
    }

    // 5. Account status validation
    if ($user['is_active'] != 1) {
        throw new Exception("Your account is inactive. Please contact the system administrator.");
    }

    // 6. Success: Set Secure Session Variables
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role_name'] = $user['role_name'];

    // 7. Silent background tasks (If these fail, the user is still logged in)
    try {
        $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
        $updateStmt->execute([$user['user_id']]);

        $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description, ip_address) VALUES (?, 'LOGIN', ?, ?)");
        $logStmt->execute([$user['user_id'], "User successfully logged into the system.", $_SERVER['REMOTE_ADDR']]);
    } catch (Exception $e) {
        // Silently ignore tracking errors to ensure core login functionality works
    }

    // 8. Dynamic Role-Based Redirect Logic
    $redirect = '/KakaiOnesys/frontend/dashboard/admin_dashboard.php'; // Default fallback

    if ($user['role_name'] === 'Cashier') {
        $redirect = '/KakaiOnesys/frontend/daily_operations/pos.php';
    } elseif ($user['role_name'] === 'Stockman') {
        $redirect = '/KakaiOnesys/frontend/inventory_logistics/receive_shipment.php';
    }

    // 9. Send successful JSON response
    echo json_encode([
        'success' => true,
        'message' => 'Login successful!',
        'redirect' => $redirect
    ]);
} catch (Exception $e) {
    // 10. Catch ALL errors here and return a clean JSON error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
