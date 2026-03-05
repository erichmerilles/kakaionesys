<?php
// backend/business_intelligence/get_dashboard_data.php
require_once __DIR__ . '/../../frontend/includes/auth_check.php';

// Ensure only authorized users can load this data
requirePermission('can_view_dashboard'); // Admin has bypass built-in

global $pdo;

// 1. Fetch KPI: Total Sales (Today)
$stmtSales = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM sales_transactions WHERE DATE(transaction_date) = CURDATE()");
$kpi_total_sales = $stmtSales->fetchColumn();

// 2. Fetch KPI: Net Profit (Today)
// Profit = Selling Price - Wholesale Price (Cost)
$stmtProfit = $pdo->query("
    SELECT COALESCE(SUM(si.subtotal - (i.wholesale_price * si.quantity)), 0) 
    FROM sales_items si
    JOIN sales_transactions st ON si.transaction_id = st.transaction_id
    JOIN inventory i ON si.product_id = i.product_id
    WHERE DATE(st.transaction_date) = CURDATE()
");
$kpi_net_profit = $stmtProfit->fetchColumn();

// 3. Fetch KPI: Critical Stocks (Store Shelf)
$stmtCrit = $pdo->query("SELECT COUNT(*) FROM inventory WHERE store_shelf_pcs <= critical_level_pcs");
$kpi_crit_stocks = $stmtCrit->fetchColumn();

// 4. Fetch 3-Tier Inventory Overview
$stmtInventory = $pdo->query("
    SELECT product_id, product_name, wholesale_boxes, retail_warehouse_pcs, store_shelf_pcs, critical_level_pcs, pcs_per_box 
    FROM inventory 
    ORDER BY product_name ASC
");
$inventory_data = $stmtInventory->fetchAll();

// 5. Fetch Top Selling Products
$stmtTopSelling = $pdo->query("
    SELECT i.product_name, c.category_name, SUM(si.quantity) as total_sold 
    FROM sales_items si
    JOIN inventory i ON si.product_id = i.product_id
    LEFT JOIN categories c ON i.category_id = c.category_id
    GROUP BY i.product_id 
    ORDER BY total_sold DESC 
    LIMIT 5
");
$top_selling_data = $stmtTopSelling->fetchAll();
