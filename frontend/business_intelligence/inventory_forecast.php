<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../../backend/business_intelligence/get_forecast.php';
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white p-3 border-bottom border-secondary">
        <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-arrow-trend-up me-2 text-warning"></i> Inventory Forecast & Stock Out Predictions</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info border-0 mb-4">
            <i class="fa-solid fa-robot me-2"></i> <strong>Proactive Planning:</strong> Predictions are based on the average daily sales recorded over the last 30 days.
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Product Name</th>
                        <th class="text-center">Total Pcs (All Tiers)</th>
                        <th class="text-center">Avg Daily Sales</th>
                        <th class="text-center">Est. Days Left</th>
                        <th>Forecast Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($forecast_data as $row): ?>
                        <tr>
                            <td class="fw-bold"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td class="text-center"><?php echo number_format($row['total_stock']); ?></td>
                            <td class="text-center"><?php echo $row['velocity']; ?> pcs/day</td>
                            <td class="text-center">
                                <span class="h5 mb-0 fw-bold <?php echo ($row['days_left'] <= 7) ? 'text-danger' : 'text-dark'; ?>">
                                    <?php echo ($row['days_left'] >= 999) ? '∞' : $row['days_left']; ?>
                                </span>
                                <small class="text-muted d-block">days remaining</small>
                            </td>
                            <td>
                                <?php if ($row['status'] === 'CRITICAL'): ?>
                                    <div class="progress mb-1" style="height: 10px;">
                                        <div class="progress-bar bg-danger" style="width: 20%"></div>
                                    </div>
                                    <span class="badge bg-danger">RESTOCK IMMEDIATELY</span>
                                <?php elseif ($row['status'] === 'WARNING'): ?>
                                    <div class="progress mb-1" style="height: 10px;">
                                        <div class="progress-bar bg-warning" style="width: 50%"></div>
                                    </div>
                                    <span class="badge bg-warning text-dark">PREPARE REORDER</span>
                                <?php else: ?>
                                    <div class="progress mb-1" style="height: 10px;">
                                        <div class="progress-bar bg-success" style="width: 100%"></div>
                                    </div>
                                    <span class="badge bg-success">STOCK HEALTHY</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>