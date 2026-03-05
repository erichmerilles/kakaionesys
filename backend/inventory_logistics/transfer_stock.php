<?php
// backend/inventory_logistics/transfer_stock.php
require_once __DIR__ . '/../../frontend/includes/auth_check.php';

// Enforce security for Stockman or Admin roles
requirePermission('can_manage_inventory');
verifyCSRF();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $transfer_qty = filter_input(INPUT_POST, 'transfer_qty', FILTER_VALIDATE_INT);

    if (!$product_id || !$transfer_qty || $transfer_qty <= 0) {
        header("Location: /KakaiOnesys/frontend/inventory_logistics/stock_transfer.php?status=invalid_input");
        exit;
    }

    global $pdo;

    try {
        $pdo->beginTransaction();

        // 1. Check current warehouse stock levels
        $stmt = $pdo->prepare("SELECT product_name, retail_warehouse_pcs FROM inventory WHERE product_id = :id FOR UPDATE");
        $stmt->execute(['id' => $product_id]);
        $item = $stmt->fetch();

        if (!$item) {
            throw new Exception("Product not found.");
        }

        if ($item['retail_warehouse_pcs'] < $transfer_qty) {
            throw new Exception("Insufficient stock in Retail Warehouse.");
        }

        // 2. Perform the transfer: Deduct from Warehouse, Add to Store Shelf
        $updateStmt = $pdo->prepare("
            UPDATE inventory 
            SET retail_warehouse_pcs = retail_warehouse_pcs - :qty, 
                store_shelf_pcs = store_shelf_pcs + :qty 
            WHERE product_id = :id
        ");
        $updateStmt->execute([
            'qty' => $transfer_qty,
            'id' => $product_id
        ]);

        // 3. Log the movement in the audit trail
        $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description, ip_address) VALUES (:uid, :action, :desc, :ip)");
        $description = "Transferred {$transfer_qty} pcs of {$item['product_name']} from Warehouse to Store Shelf.";
        $logStmt->execute([
            'uid' => $_SESSION['user_id'],
            'action' => 'STOCK_TRANSFER',
            'desc' => $description,
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);

        $pdo->commit();
        header("Location: /KakaiOnesys/frontend/inventory_logistics/stock_transfer.php?status=success&qty=" . $transfer_qty);
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: /KakaiOnesys/frontend/inventory_logistics/stock_transfer.php?status=error&msg=" . urlencode($e->getMessage()));
        exit;
    }
}
