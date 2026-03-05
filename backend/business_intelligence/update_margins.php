<?php
// backend/business_intelligence/update_margins.php
require_once __DIR__ . '/../../frontend/includes/auth_check.php';

if ($_SESSION['role_name'] !== 'Admin') die("Unauthorized");
verifyCSRF();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $wholesale = filter_input(INPUT_POST, 'wholesale_price', FILTER_VALIDATE_FLOAT);
    $retail = filter_input(INPUT_POST, 'retail_price', FILTER_VALIDATE_FLOAT);

    global $pdo;

    try {
        $stmt = $pdo->prepare("UPDATE inventory SET wholesale_price = ?, retail_price = ? WHERE product_id = ?");
        $stmt->execute([$wholesale, $retail, $pid]);

        // Log price change
        $stmtLog = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description, ip_address) VALUES (?, 'PRICE_UPDATE', ?, ?)");
        $stmtLog->execute([$_SESSION['user_id'], "Updated pricing for Product ID: $pid", $_SERVER['REMOTE_ADDR']]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
