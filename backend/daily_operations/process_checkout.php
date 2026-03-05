<?php
// backend/daily_operations/process_checkout.php
require_once __DIR__ . '/../../frontend/includes/auth_check.php';

// Security: Check for POS permissions and verify CSRF
requirePermission('can_view_pos');
verifyCSRF();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart = json_decode($_POST['cart_data'], true);
    $amount_tendered = filter_var($_POST['amount_tendered'], FILTER_VALIDATE_FLOAT);

    if (empty($cart) || $amount_tendered === false) {
        die("Invalid transaction data.");
    }

    global $pdo;

    try {
        $pdo->beginTransaction();

        $total_amount = 0;
        $receipt_no = "RCPT-" . time();
        $items_to_process = [];

        foreach ($cart as $item) {
            // Fetch latest price and shelf stock from DB to prevent browser manipulation
            $stmt = $pdo->prepare("SELECT product_name, retail_price, store_shelf_pcs FROM inventory WHERE product_id = ? FOR UPDATE");
            $stmt->execute([$item['id']]);
            $product = $stmt->fetch();

            if (!$product || $product['store_shelf_pcs'] < $item['quantity']) {
                throw new Exception("Insufficient stock for: " . ($product['product_name'] ?? "Unknown"));
            }

            $subtotal = $product['retail_price'] * $item['quantity'];
            $total_amount += $subtotal;

            $items_to_process[] = [
                'id' => $item['id'],
                'qty' => $item['quantity'],
                'price' => $product['retail_price'],
                'subtotal' => $subtotal
            ];
        }

        if ($amount_tendered < $total_amount) {
            throw new Exception("Tendered amount is insufficient.");
        }

        $change = $amount_tendered - $total_amount;

        // 1. Save Transaction
        $stmtTrans = $pdo->prepare("INSERT INTO sales_transactions (receipt_no, user_id, total_amount, amount_tendered, change_amount) VALUES (?, ?, ?, ?, ?)");
        $stmtTrans->execute([$receipt_no, $_SESSION['user_id'], $total_amount, $amount_tendered, $change]);
        $transaction_id = $pdo->lastInsertId();

        // 2. Save Items & Deduct Shelf Stock
        $stmtItem = $pdo->prepare("INSERT INTO sales_items (transaction_id, product_id, quantity, price_at_sale, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stmtUpdate = $pdo->prepare("UPDATE inventory SET store_shelf_pcs = store_shelf_pcs - ? WHERE product_id = ?");

        foreach ($items_to_process as $i) {
            $stmtItem->execute([$transaction_id, $i['id'], $i['qty'], $i['price'], $i['subtotal']]);
            $stmtUpdate->execute([$i['qty'], $i['id']]);
        }

        // 3. Log Activity
        $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description, ip_address) VALUES (?, 'POS_CHECKOUT', ?, ?)");
        $logStmt->execute([$_SESSION['user_id'], "Processed Sale $receipt_no - Total: ₱$total_amount", $_SERVER['REMOTE_ADDR']]);

        $pdo->commit();
        echo json_encode(['success' => true, 'receipt' => $receipt_no, 'change' => $change]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
