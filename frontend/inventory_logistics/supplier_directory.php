<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

global $pdo;
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY supplier_name ASC")->fetchAll();
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white p-3 border-bottom border-secondary d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-truck-field me-2"></i> Supplier Directory</h5>
        <button class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
            <i class="fa-solid fa-plus me-1"></i> New Supplier
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Supplier Name</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suppliers as $s): ?>
                        <tr>
                            <td class="fw-bold"><?php echo htmlspecialchars($s['supplier_name']); ?></td>
                            <td><?php echo htmlspecialchars($s['contact_person']); ?></td>
                            <td><?php echo htmlspecialchars($s['contact_number']); ?></td>
                            <td class="small"><?php echo htmlspecialchars($s['address']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addSupplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="supplierForm">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add New Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="mb-3">
                        <label class="form-label">Supplier Name</label>
                        <input type="text" class="form-control" name="supplier_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Person</label>
                        <input type="text" class="form-control" name="contact_person">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-control" name="contact_number">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="saveSupplier()">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    async function saveSupplier() {
        const formData = new window.FormData(document.getElementById('supplierForm'));
        const res = await fetch('/KakaiOnesys/backend/inventory_logistics/manage_suppliers.php', {
            method: 'POST',
            body: formData
        });
        const result = await res.json();
        if (result.success) {
            Swal.fire('Success', 'Supplier added to directory.', 'success').then(() => location.reload());
        } else {
            Swal.fire('Error', result.message, 'error');
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>