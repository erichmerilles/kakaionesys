<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

global $pdo;
$items = $pdo->query("SELECT product_id, product_name, wholesale_price, retail_price FROM inventory ORDER BY product_name ASC")->fetchAll();
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white p-3 border-bottom border-secondary">
        <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-tags me-2 text-warning"></i> Price & Margin Manager</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Product Name</th>
                        <th width="150">Wholesale (Cost)</th>
                        <th width="150">Retail (Sell)</th>
                        <th class="text-center">Markup</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item):
                        $markup = $item['retail_price'] - $item['wholesale_price'];
                        $margin_pct = ($item['retail_price'] > 0) ? ($markup / $item['retail_price']) * 100 : 0;
                    ?>
                        <tr>
                            <form class="price-form">
                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                                <td class="fw-bold"><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td><input type="number" step="0.01" class="form-control form-control-sm" name="wholesale_price" value="<?php echo $item['wholesale_price']; ?>"></td>
                                <td><input type="number" step="0.01" class="form-control form-control-sm" name="retail_price" value="<?php echo $item['retail_price']; ?>"></td>
                                <td class="text-center">
                                    <span class="badge bg-success">₱<?php echo number_format($markup, 2); ?></span>
                                    <small class="text-muted d-block"><?php echo number_format($margin_pct, 1); ?>% margin</small>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-dark" onclick="updatePrice(this.form)">
                                        <i class="fa-solid fa-save"></i>
                                    </button>
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    async function updatePrice(form) {
        const formData = new window.FormData(form);
        const res = await fetch('/KakaiOnesys/backend/business_intelligence/update_margins.php', {
            method: 'POST',
            body: formData
        });
        const result = await res.json();
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Pricing Updated',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            });
            // Optionally refresh page or recalculate UI values
        } else {
            Swal.fire('Error', result.message, 'error');
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>