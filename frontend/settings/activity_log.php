<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

global $pdo;

// Fetch logs joining with users to show who did what
$stmt = $pdo->query("
    SELECT al.*, u.full_name 
    FROM activity_logs al 
    LEFT JOIN users u ON al.user_id = u.user_id 
    ORDER BY al.created_at DESC 
    LIMIT 100
");
$logs = $stmt->fetchAll();
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white p-3 border-bottom border-secondary d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> System Audit Trail</h5>
        <button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print"></i> Print Log</button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="small text-muted"><?php echo date('M d, Y h:i A', strtotime($log['created_at'])); ?></td>
                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($log['full_name'] ?? 'System'); ?></span></td>
                            <td>
                                <?php
                                $color = 'secondary';
                                if ($log['action_type'] == 'POS_CHECKOUT') $color = 'success';
                                if ($log['action_type'] == 'EXPLODE_BULK') $color = 'warning text-dark';
                                if ($log['action_type'] == 'LOGIN') $color = 'info';
                                ?>
                                <span class="badge bg-<?php echo $color; ?>"><?php echo $log['action_type']; ?></span>
                            </td>
                            <td class="small"><?php echo htmlspecialchars($log['description']); ?></td>
                            <td class="text-muted x-small"><?php echo $log['ip_address']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>