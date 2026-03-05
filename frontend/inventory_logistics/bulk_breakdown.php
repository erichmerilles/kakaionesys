<?php
// backend/inventory_logistics/explode_bulk.php
require_once __DIR__ . '/../../frontend/includes/auth_check.php';

// Enforce security
requirePermission('can_manage_inventory'); // Allows Admin and Stockman
verifyCSRF();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $boxes_to_explode = filter_input(INPUT_POST, 'boxes_to_explode', FILTER_VALIDATE_INT);

    if (!$product_id || !$boxes_to_explode || $boxes_to_explode <= 0) {
        header("Location: /KakaiOnesys/frontend/inventory_logistics/bulk_breakdown.php?status=invalid_input");
        exit;
    }

    global $pdo;

    try {
        // Begin transaction to ensure data integrity
        $pdo->beginTransaction();

        // 1. Lock the specific row and fetch current stock and conversion rate
        $stmt = $pdo->prepare("SELECT product_name, wholesale_boxes, pcs_per_box FROM inventory WHERE product_id = :id FOR UPDATE");
        $stmt->execute(['id' => $product_id]);
        $item = $stmt->fetch();

        if (!$item) {
            throw new Exception("Product not found.");
        }

        if ($item['wholesale_boxes'] < $boxes_to_explode) {
            throw new Exception("Insufficient wholesale boxes available.");
        }

        // 2. Calculate new pieces
        $pcs_to_add = $boxes_to_explode * $item['pcs_per_box'];

        // 3. Update the inventory tiers
        $updateStmt = $pdo->prepare("
            UPDATE inventory 
            SET wholesale_boxes = wholesale_boxes - :boxes, 
                retail_warehouse_pcs = retail_warehouse_pcs + :pcs 
            WHERE product_id = :id
        ");
        $updateStmt->execute([
            'boxes' => $boxes_to_explode,
            'pcs' => $pcs_to_add,
            'id' => $product_id
        ]);

        // 4. Log the action in the audit trail
        $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description, ip_address) VALUES (:uid, :action, :desc, :ip)");
        $description = "Exploded {$boxes_to_explode} box(es) of {$item['product_name']} into {$pcs_to_add} pieces.";
        $logStmt->execute([
            'uid' => $_SESSION['user_id'],
            'action' => 'EXPLODE_BULK',
            'desc' => $description,
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);

        // Commit transaction
        $pdo->commit();
        header("Location: /KakaiOnesys/frontend/inventory_logistics/bulk_breakdown.php?status=success&exploded=" . $pcs_to_add);
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        // Pass error message securely to the frontend
        header("Location: /KakaiOnesys/frontend/inventory_logistics/bulk_breakdown.php?status=error&msg=" . urlencode($e->getMessage()));
        exit;
    }
}
