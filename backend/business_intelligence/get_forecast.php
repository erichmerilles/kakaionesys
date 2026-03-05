<?php
// backend/business_intelligence/get_forecast.php
require_once __DIR__ . '/../../frontend/includes/auth_check.php';

// Only Admin or users with report permissions
requirePermission('can_view_reports');

global $pdo;

try {
    // 1. Calculate Daily Sales Velocity (Average pieces sold per day over last 30 days)
    $stmtVelocity = $pdo->query("
        SELECT product_id, SUM(quantity) / 30 as daily_velocity 
        FROM sales_items si
        JOIN sales_transactions st ON si.transaction_id = st.transaction_id
        WHERE st.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY product_id
    ");
    $velocities = $stmtVelocity->fetchAll(PDO::FETCH_KEY_PAIR);

    // 2. Fetch All Inventory Tiers for Calculation
    $stmtInv = $pdo->query("SELECT * FROM inventory ORDER BY product_name ASC");
    $items = $stmtInv->fetchAll();

    $forecast_data = [];
    foreach ($items as $item) {
        $pid = $item['product_id'];

        // Calculate Total Pieces available across all 3 tiers
        $total_pcs = ($item['wholesale_boxes'] * $item['pcs_per_box']) +
            $item['retail_warehouse_pcs'] +
            $item['store_shelf_pcs'];

        $daily_avg = $velocities[$pid] ?? 0;

        // Calculate Estimated Days Left
        if ($daily_avg > 0) {
            $days_left = floor($total_pcs / $daily_avg);
        } else {
            $days_left = ($total_pcs > 0) ? 999 : 0; // 999 indicates "No Sales but has stock"
        }

        $forecast_data[] = [
            'name' => $item['product_name'],
            'total_stock' => $total_pcs,
            'velocity' => number_format($daily_avg, 2),
            'days_left' => $days_left,
            'status' => ($days_left <= 7) ? 'CRITICAL' : (($days_left <= 14) ? 'WARNING' : 'HEALTHY')
        ];
    }
} catch (PDOException $e) {
    die("Forecasting Error: " . $e->getMessage());
}
