<?php
// backend/business_intelligence/get_profitability.php
require_once __DIR__ . '/../../frontend/includes/auth_check.php';

// Only Admin can access profitability reports
if ($_SESSION['role_name'] !== 'Admin') {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access.']));
}

global $pdo;

$filter = $_GET['filter'] ?? 'daily';
$dateQuery = "DATE(st.transaction_date) = CURDATE()"; // Default Daily

if ($filter === 'weekly') {
    $dateQuery = "st.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
} elseif ($filter === 'monthly') {
    $dateQuery = "st.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
}

try {
    // Aggregated SQL to calculate Revenue vs Cost
    $stmt = $pdo->prepare("
        SELECT 
            i.product_name,
            SUM(si.quantity) as total_qty,
            SUM(si.subtotal) as total_revenue,
            SUM(i.wholesale_price * si.quantity) as total_cost,
            SUM(si.subtotal - (i.wholesale_price * si.quantity)) as total_profit
        FROM sales_items si
        JOIN sales_transactions st ON si.transaction_id = st.transaction_id
        JOIN inventory i ON si.product_id = i.product_id
        WHERE $dateQuery
        GROUP BY i.product_id
        ORDER BY total_profit DESC
    ");
    $stmt->execute();
    $report_data = $stmt->fetchAll();

    // Summary Totals
    $summary = [
        'revenue' => 0,
        'cost' => 0,
        'profit' => 0
    ];

    foreach ($report_data as $row) {
        $summary['revenue'] += $row['total_revenue'];
        $summary['cost'] += $row['total_cost'];
        $summary['profit'] += $row['total_profit'];
    }
} catch (PDOException $e) {
    die("Report Generation Error: " . $e->getMessage());
}
