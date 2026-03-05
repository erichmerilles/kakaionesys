<?php
// frontend/dashboard/admin_dashboard.php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../../backend/business_intelligence/get_dashboard_data.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Admin Dashboard</h2>
    <span class="text-muted"><i class="fa-regular fa-calendar"></i> <?php echo date('F d, Y'); ?></span>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card kpi-card bg-primary text-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1">Total Sales (Today)</h6>
                    <h3 class="mb-0">₱<?php echo number_format($kpi_total_sales, 2); ?></h3>
                </div>
                <i class="fa-solid fa-peso-sign fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card bg-success text-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1">Net Profit (Today)</h6>
                    <h3 class="mb-0">₱<?php echo number_format($kpi_net_profit, 2); ?></h3>
                </div>
                <i class="fa-solid fa-chart-line fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card bg-danger text-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1">Critical Stocks</h6>
                    <h3 class="mb-0"><?php echo $kpi_crit_stocks; ?> Items</h3>
                </div>
                <i class="fa-solid fa-triangle-exclamation fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card bg-warning text-dark p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1">Expiring (30 Days)</h6>
                    <h3 class="mb-0">0 Items</h3>
                </div>
                <i class="fa-solid fa-hourglass-half fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom border-secondary p-3">
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-layer-group text-info me-2"></i> 3-Tier Stock Overview</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th class="text-center">Wholesale (Boxes)</th>
                                <th class="text-center">Warehouse (Pcs)</th>
                                <th class="text-center">Store Shelf (Pcs)</th>
                                <th>Suggested Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventory_data as $item):
                                // Dynamic AI-like Suggested Action Logic
                                $action = '<span class="badge bg-success">Adequate</span>';

                                if ($item['store_shelf_pcs'] <= $item['critical_level_pcs']) {
                                    if ($item['retail_warehouse_pcs'] > 0) {
                                        $action = '<a href="/KakaiOnesys/frontend/inventory_logistics/stock_transfer.php?id=' . $item['product_id'] . '" class="badge bg-primary text-decoration-none">Transfer to Shelf</a>';
                                    } elseif ($item['wholesale_boxes'] > 0) {
                                        $action = '<a href="/KakaiOnesys/frontend/inventory_logistics/bulk_breakdown.php?id=' . $item['product_id'] . '" class="badge bg-warning text-dark text-decoration-none">Explode Bulk Box</a>';
                                    } else {
                                        $action = '<span class="badge bg-danger">Reorder from Supplier</span>';
                                    }
                                }
                            ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td class="text-center"><span class="badge bg-secondary rounded-pill"><?php echo $item['wholesale_boxes']; ?></span></td>
                                    <td class="text-center"><span class="badge bg-secondary rounded-pill"><?php echo $item['retail_warehouse_pcs']; ?></span></td>
                                    <td class="text-center">
                                        <span class="badge <?php echo ($item['store_shelf_pcs'] <= $item['critical_level_pcs']) ? 'bg-danger' : 'bg-success'; ?> rounded-pill">
                                            <?php echo $item['store_shelf_pcs']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $action; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom border-secondary p-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-fire text-danger me-2"></i> Top Selling</h5>
                <select class="form-select form-select-sm w-auto">
                    <option value="all">All Categories</option>
                    <option value="nuts">Nuts</option>
                    <option value="gummies">Gummies</option>
                </select>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php if (count($top_selling_data) > 0): ?>
                        <?php foreach ($top_selling_data as $top_item): ?>
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($top_item['product_name']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($top_item['category_name'] ?? 'Uncategorized'); ?></small>
                                </div>
                                <span class="badge bg-primary rounded-pill"><?php echo $top_item['total_sold']; ?> sold</span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item px-0 text-center text-muted">No sales data yet.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>