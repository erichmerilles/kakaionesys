<?php
// frontend/inventory_logistics/stock_transfer.php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

global $pdo;
// Fetch products that have stock in the Retail Warehouse
$stmt = $pdo->query("SELECT product_id, product_name, retail_warehouse_pcs, store_shelf_pcs FROM inventory WHERE retail_warehouse_pcs > 0 ORDER BY product_name ASC");
$items = $stmt->fetchAll();

// Catch pre-selected ID from Dashboard suggested actions
$preselected_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold"><i class="fa-solid fa-people-carry-box text-primary me-2"></i> Stock Transfer</h2>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom border-secondary p-3">
                <h5 class="mb-0 text-primary fw-bold">Warehouse to Store Shelf</h5>
            </div>
            <div class="card-body">
                <form action="/KakaiOnesys/backend/inventory_logistics/transfer_stock.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <div class="mb-3">
                        <label for="product_id" class="form-label fw-semibold">Select Product</label>
                        <select class="form-select" id="product_id" name="product_id" required>
                            <option value="" disabled <?php echo !$preselected_id ? 'selected' : ''; ?>>Select an item to transfer...</option>
                            <?php foreach ($items as $row): ?>
                                <option value="<?php echo $row['product_id']; ?>"
                                    data-warehouse="<?php echo $row['retail_warehouse_pcs']; ?>"
                                    data-shelf="<?php echo $row['store_shelf_pcs']; ?>"
                                    <?php echo ($preselected_id == $row['product_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($row['product_name']); ?>
                                    (Warehouse: <?php echo $row['retail_warehouse_pcs']; ?> pcs)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label for="transfer_qty" class="form-label fw-semibold">Quantity to Move</label>
                            <input type="number" class="form-control form-control-lg" id="transfer_qty" name="transfer_qty" min="1" required>
                            <div class="form-text" id="transferLimitMsg">
                                Select a product to see availability.
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2">
                        <i class="fa-solid fa-arrow-right-arrow-left me-2"></i> Confirm Transfer
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card bg-primary text-white border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="fw-bold"><i class="fa-solid fa-shop me-2"></i> Store Ready</h5>
                <p class="small mb-0">Moving stock to the <strong>Store Shelf</strong> updates the inventory that the Cashier can see and sell. Ensure the physical stock matches the system transfer.</p>
            </div>
        </div>

        <div id="statusCard" class="card shadow-sm border-0 d-none">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small fw-bold">Current Distribution</h6>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Retail Warehouse:</span>
                    <span id="warehouseVal" class="badge bg-secondary">0</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Store Shelf:</span>
                    <span id="shelfVal" class="badge bg-success">0</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const productSelect = document.getElementById('product_id');
        const qtyInput = document.getElementById('transfer_qty');
        const limitMsg = document.getElementById('transferLimitMsg');
        const statusCard = document.getElementById('statusCard');

        function updateStockDisplay() {
            const option = productSelect.options[productSelect.selectedIndex];
            if (option.value) {
                const warehouse = option.getAttribute('data-warehouse');
                const shelf = option.getAttribute('data-shelf');

                qtyInput.setAttribute('max', warehouse);
                limitMsg.innerHTML = `<span class="text-primary">Available in warehouse: <strong>${warehouse} pcs</strong></span>`;

                // Show status card
                statusCard.classList.remove('d-none');
                document.getElementById('warehouseVal').innerText = warehouse + " pcs";
                document.getElementById('shelfVal').innerText = shelf + " pcs";
            }
        }

        productSelect.addEventListener('change', updateStockDisplay);
        updateStockDisplay(); // Run once for preselected items

        // Handle SweetAlerts from PHP feedback
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('status') === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Transfer Successful',
                text: `Successfully moved ${urlParams.get('qty')} pieces to the store shelf.`,
                confirmButtonColor: '#0d6efd'
            }).then(() => {
                window.history.replaceState(null, null, window.location.pathname);
            });
        } else if (urlParams.get('status') === 'error') {
            Swal.fire({
                icon: 'error',
                title: 'Transfer Failed',
                text: urlParams.get('msg'),
                confirmButtonColor: '#dc3545'
            }).then(() => {
                window.history.replaceState(null, null, window.location.pathname);
            });
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>