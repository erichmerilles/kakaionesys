<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

global $pdo;
// Fetch all items to populate the dropdown
$products = $pdo->query("SELECT product_id, product_name, wholesale_boxes, retail_warehouse_pcs, store_shelf_pcs FROM inventory ORDER BY product_name ASC")->fetchAll();
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white p-3 border-bottom border-secondary">
        <h5 class="mb-0 fw-bold text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i> Record Damage or Loss</h5>
    </div>
    <div class="card-body">
        <form id="damageForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">1. Select Product</label>
                    <select class="form-select" name="product_id" id="product_id" required onchange="updateStockPreview()">
                        <option value="" selected disabled>Choose item...</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?php echo $p['product_id']; ?>"
                                data-wholesale="<?php echo $p['wholesale_boxes']; ?>"
                                data-warehouse="<?php echo $p['retail_warehouse_pcs']; ?>"
                                data-shelf="<?php echo $p['store_shelf_pcs']; ?>">
                                <?php echo htmlspecialchars($p['product_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">2. Location Tier</label>
                    <select class="form-select" name="tier" id="tier" required onchange="updateStockPreview()">
                        <option value="store_shelf_pcs">Store Shelf (pcs)</option>
                        <option value="retail_warehouse_pcs">Retail Warehouse (pcs)</option>
                        <option value="wholesale_boxes">Wholesale (Boxes)</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">3. Quantity Lost</label>
                    <input type="number" class="form-control" name="quantity" min="1" required>
                    <div class="form-text" id="stockPreview">Available in selected tier: 0</div>
                </div>

                <div class="col-md-8">
                    <label class="form-label fw-bold">4. Reason / Remarks</label>
                    <input type="text" class="form-control" name="reason" placeholder="e.g., Spoilage, Damaged packaging, Missing" required>
                </div>
            </div>

            <button type="button" class="btn btn-danger mt-4 fw-bold px-5" onclick="submitLoss()">
                <i class="fa-solid fa-file-signature me-2"></i> LOG LOSS ENTRY
            </button>
        </form>
    </div>
</div>

<script>
    function updateStockPreview() {
        const product = document.getElementById('product_id');
        const tier = document.getElementById('tier').value;
        const selected = product.options[product.selectedIndex];

        if (selected.value) {
            let stock = 0;
            if (tier === 'wholesale_boxes') stock = selected.getAttribute('data-wholesale');
            else if (tier === 'retail_warehouse_pcs') stock = selected.getAttribute('data-warehouse');
            else stock = selected.getAttribute('data-shelf');

            document.getElementById('stockPreview').innerText = `Available in selected tier: ${stock}`;
        }
    }

    async function submitLoss() {
        const formData = new window.FormData(document.getElementById('damageForm'));

        const res = await fetch('/KakaiOnesys/backend/daily_operations/log_damage_loss.php', {
            method: 'POST',
            body: formData
        });

        const result = await res.json();
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Loss Recorded',
                text: 'Inventory has been adjusted.',
                confirmButtonColor: '#dc3545'
            }).then(() => location.reload());
        } else {
            Swal.fire('Error', result.message, 'error');
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>