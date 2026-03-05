<?php
// backend/utils/activity_logger.php
require_once __DIR__ . '/../../frontend/includes/auth_check.php';

/**
 * Global function to record user actions into the database.
 * Supports the professional audit trail requirement.
 */
function logActivity($action_type, $description)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'] ?? null,
            $action_type,
            $description,
            $_SERVER['REMOTE_ADDR']
        ]);
    } catch (PDOException $e) {
        // Silently fail logging to prevent stopping the main application flow
        error_log("Activity Log Error: " . $e->getMessage());
    }
}
