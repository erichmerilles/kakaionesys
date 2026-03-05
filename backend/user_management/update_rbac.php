<?php
// backend/user_management/update_rbac.php
require_once __DIR__ . '/../../frontend/includes/auth_check.php';

// Strictly Admin only
if ($_SESSION['role_name'] !== 'Admin') {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access.']));
}

verifyCSRF();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $permissions = $_POST['perms'] ?? []; // Array of permission keys

    if (!$target_user_id) {
        die(json_encode(['success' => false, 'message' => 'Invalid user ID.']));
    }

    global $pdo;

    try {
        $pdo->beginTransaction();

        // 1. Clear existing granular permissions
        $stmtDelete = $pdo->prepare("DELETE FROM user_permissions WHERE user_id = ?");
        $stmtDelete->execute([$target_user_id]);

        // 2. Insert new granted permissions
        $stmtInsert = $pdo->prepare("INSERT INTO user_permissions (user_id, permission_key, is_granted) VALUES (?, ?, 1)");
        foreach ($permissions as $key) {
            $stmtInsert->execute([$target_user_id, $key]);
        }

        // 3. Log this security change
        $stmtLog = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description, ip_address) VALUES (?, 'RBAC_UPDATE', ?, ?)");
        $stmtLog->execute([$_SESSION['user_id'], "Updated permissions for User ID: $target_user_id", $_SERVER['REMOTE_ADDR']]);

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
