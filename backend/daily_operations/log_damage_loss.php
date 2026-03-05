<?php
// backend/daily_operations/log_damage_loss.php
require_once __DIR__ . '/../../frontend/includes/auth_check.php';

// Security: Check for specific damage entry permissions and verify CSRF
requirePermission('can_log_damage');
verifyCSRF();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $tier = $_POST['tier']; // 'wholesale_boxes', 'retail_warehouse_pcs', or 'store_shelf_pcs'
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $reason = htmlspecialchars($_POST['reason']);

    if (!$product_id || !$quantity || $quantity <= 0 || !in_array($tier, ['wholesale_boxes', 'retail_warehouse_pcs', 'store_shelf_pcs'])) {
        die(json_encode(['success' => false, 'message' => 'Invalid data provided.']));
    }

    global $pdo;

    try {
        $pdo->beginTransaction();

        // 1. Verify current stock in the selected tier
        $stmt = $pdo->prepare("SELECT product_name, $tier FROM inventory WHERE product_id = ? FOR UPDATE");
        $stmt->execute([$product_id]);
        $item = $stmt->fetch();

        if (!$item || $item[$tier] < $quantity) {
            throw new Exception("Insufficient stock in the selected tier to record this loss.");
        }

        // 2. Deduct the loss from the inventory
        $updateStmt = $pdo->prepare("UPDATE inventory SET $tier = $tier - ? WHERE product_id = ?");
        $updateStmt->execute([$quantity, $product_id]);

        // 3. Log the activity with the specific reason
        $logDescription = "Loss Recorded: $quantity unit(s) of '{$item['product_name']}' from $tier. Reason: $reason";
        $stmtLog = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description, ip_address) VALUES (?, 'DAMAGE_LOSS', ?, ?)");
        $stmtLog->execute([$_SESSION['user_id'], $logDescription, $_SERVER['REMOTE_ADDR']]);

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
