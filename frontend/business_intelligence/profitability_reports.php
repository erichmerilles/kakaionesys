<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../../backend/business_intelligence/get_profitability.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold"><i class="fa-solid fa-file-invoice-dollar text-success me-2"></i> Profitability Analysis</h2>

    <div class="btn-group shadow-sm">
        <a href="?filter=daily" class="btn btn-outline-dark <?php echo ($filter == 'daily') ? 'active' : ''; ?>">Daily</a>
        <a href="?filter=weekly" class="btn btn-outline-dark <?php echo ($filter == 'weekly') ? 'active' : ''; ?>">Weekly</a>
        <a href="?filter=monthly" class="btn btn-outline-dark <?php echo ($filter == 'monthly') ? 'active' : ''; ?>">Monthly</a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3">
            <h6 class="text-muted text-uppercase small">Total Revenue</h6>
            <h3 class="fw-bold text-primary">₱<?php echo number_format($summary['revenue'], 2); ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3">
            <h6 class="text-muted text-uppercase small">Total Cost (COGS)</h6>
            <h3 class="fw-bold text-secondary">₱<?php echo number_format($summary['cost'], 2); ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 bg-success text-white">
            <h6 class="text-uppercase small">Net Profit</h6>
            <h3 class="fw-bold">₱<?php echo number_format($summary['profit'], 2); ?></h3>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white p-3 border-bottom border-secondary">
        <h5 class="mb-0 fw-bold">Itemized Profit Breakdown (<?php echo ucfirst($filter); ?>)</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Product Name</th>
                        <th class="text-center">Sold</th>
                        <th class="text-end">Revenue</th>
                        <th class="text-end">Cost</th>
                        <th class="text-end">Net Profit</th>
                        <th class="text-center">Margin (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($report_data) > 0): ?>
                        <?php foreach ($report_data as $row):
                            $margin = ($row['total_revenue'] > 0) ? ($row['total_profit'] / $row['total_revenue']) * 100 : 0;
                        ?>
                            <tr>
                                <td class="fw-bold"><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td class="text-center"><?php echo $row['total_qty']; ?></td>
                                <td class="text-end">₱<?php echo number_format($row['total_revenue'], 2); ?></td>
                                <td class="text-end text-muted">₱<?php echo number_format($row['total_cost'], 2); ?></td>
                                <td class="text-end fw-bold text-success">₱<?php echo number_format($row['total_profit'], 2); ?></td>
                                <td class="text-center">
                                    <span class="badge bg-info text-dark"><?php echo number_format($margin, 1); ?>%</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No sales data found for this period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>