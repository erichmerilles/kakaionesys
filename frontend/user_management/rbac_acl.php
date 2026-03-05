<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if ($_SESSION['role_name'] !== 'Admin') {
    echo "<script>window.location.href='/KakaiOnesys/frontend/dashboard/admin_dashboard.php';</script>";
    exit;
}

global $pdo;
// Fetch all staff (excluding the main Admin to prevent self-lockout)
$users = $pdo->query("SELECT u.user_id, u.full_name, r.role_name FROM users u JOIN roles r ON u.role_id = r.role_id WHERE r.role_name != 'Admin'")->fetchAll();

// Defined permission keys for the system
$available_perms = [
    'can_view_pos' => 'Access POS Checkout',
    'can_manage_inventory' => 'Manage Inventory (Receive/Explode/Transfer)',
    'can_view_reports' => 'View Profitability & Forecasts',
    'can_log_damage' => 'Entry Damage/Loss Logs'
];
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white p-3 border-bottom border-secondary">
        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-user-shield me-2 text-danger"></i> Access Control List (RBAC)</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 border-end">
                <label class="form-label fw-bold">1. Select Staff Member</label>
                <div class="list-group">
                    <?php foreach ($users as $u): ?>
                        <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="loadPermissions(<?php echo $u['user_id']; ?>, '<?php echo addslashes($u['full_name']); ?>')">
                            <?php echo htmlspecialchars($u['full_name']); ?>
                            <span class="badge bg-secondary rounded-pill"><?php echo $u['role_name']; ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-md-8">
                <div id="permContainer" class="d-none">
                    <h6 class="fw-bold mb-3">2. Set Permissions for: <span id="targetUserName" class="text-primary"></span></h6>
                    <form id="rbacForm">
                        <input type="hidden" name="user_id" id="targetUserId">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                        <div class="row g-3">
                            <?php foreach ($available_perms as $key => $label): ?>
                                <div class="col-md-6">
                                    <div class="form-check form-switch p-3 border rounded">
                                        <input class="form-check-input" type="checkbox" name="perms[]" value="<?php echo $key; ?>" id="check_<?php echo $key; ?>">
                                        <label class="form-check-label fw-semibold" for="check_<?php echo $key; ?>"><?php echo $label; ?></label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <button type="button" class="btn btn-danger mt-4 fw-bold w-100" onclick="savePermissions()">
                            <i class="fa-solid fa-save me-2"></i> UPDATE ACCESS PERMISSIONS
                        </button>
                    </form>
                </div>
                <div id="emptyMsg" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-user-pointer fa-3x mb-3 opacity-25"></i>
                    <p>Select a staff member from the left to manage their access.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    async function loadPermissions(userId, userName) {
        document.getElementById('targetUserId').value = userId;
        document.getElementById('targetUserName').innerText = userName;
        document.getElementById('permContainer').classList.remove('d-none');
        document.getElementById('emptyMsg').classList.add('d-none');

        // Uncheck all first
        document.querySelectorAll('.form-check-input').forEach(cb => cb.checked = false);

        // Fetch current permissions (Simplified for this example)
        // In a full build, you'd fetch this via an AJAX call to a 'get_user_permissions.php'
    }

    async function savePermissions() {
        const formData = new window.FormData(document.getElementById('rbacForm'));

        const res = await fetch('/KakaiOnesys/backend/user_management/update_rbac.php', {
            method: 'POST',
            body: formData
        });

        const result = await res.json();
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Permissions Updated',
                text: 'Changes will take effect on the user\'s next action.',
                confirmButtonColor: '#dc3545'
            });
        } else {
            Swal.fire('Error', result.message, 'error');
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>