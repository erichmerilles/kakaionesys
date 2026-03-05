<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

global $pdo;
// Fetch only items available on the store shelf
$products = $pdo->query("SELECT product_id, product_name, retail_price, store_shelf_pcs FROM inventory WHERE store_shelf_pcs > 0")->fetchAll();
?>

<div class="row">
    <div class="col-md-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white p-3 border-bottom border-secondary">
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-boxes-stacked me-2"></i> Available Products</h5>
            </div>
            <div class="card-body">
                <div class="row row-cols-1 row-cols-md-3 g-3">
                    <?php foreach ($products as $p): ?>
                        <div class="col">
                            <div class="card h-100 product-card border-secondary" onclick="addToCart(<?php echo $p['product_id']; ?>, '<?php echo addslashes($p['product_name']); ?>', <?php echo $p['retail_price']; ?>, <?php echo $p['store_shelf_pcs']; ?>)">
                                <div class="card-body text-center">
                                    <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($p['product_name']); ?></h6>
                                    <p class="text-primary mb-0 fw-bold">₱<?php echo number_format($p['retail_price'], 2); ?></p>
                                    <small class="text-muted">Shelf: <?php echo $p['store_shelf_pcs']; ?> pcs</small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-dark text-white p-3">
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-cart-shopping me-2"></i> Current Order</h5>
            </div>
            <div class="card-body d-flex flex-column">
                <div class="table-responsive flex-grow-1">
                    <table class="table align-middle" id="cartTable">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cartBody">
                        </tbody>
                    </table>
                </div>

                <div class="border-top pt-3 mt-auto">
                    <div class="d-flex justify-content-between h4 fw-bold text-success">
                        <span>Total:</span>
                        <span id="cartTotal">₱0.00</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Amount Tendered</label>
                        <input type="number" class="form-control form-control-lg" id="tenderedAmount" placeholder="0.00">
                    </div>
                    <button class="btn btn-success btn-lg w-100 fw-bold" onclick="processCheckout()">
                        <i class="fa-solid fa-check-double me-2"></i> COMPLETE SALE
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let cart = [];

    function addToCart(id, name, price, stock) {
        let item = cart.find(i => i.id === id);
        if (item) {
            if (item.quantity + 1 > stock) {
                Swal.fire('Limit Reached', 'Not enough stock on shelf.', 'warning');
                return;
            }
            item.quantity++;
        } else {
            cart.push({
                id,
                name,
                price,
                quantity: 1,
                stock
            });
        }
        renderCart();
    }

    function renderCart() {
        let body = document.getElementById('cartBody');
        let total = 0;
        body.innerHTML = '';

        cart.forEach((item, index) => {
            let subtotal = item.price * item.quantity;
            total += subtotal;
            body.innerHTML += `
            <tr>
                <td>${item.name}</td>
                <td>
                    <input type="number" class="form-control form-control-sm w-50" value="${item.quantity}" onchange="updateQty(${index}, this.value)">
                </td>
                <td>₱${subtotal.toFixed(2)}</td>
                <td><button class="btn btn-sm text-danger" onclick="removeFromCart(${index})"><i class="fa-solid fa-trash"></i></button></td>
            </tr>`;
        });
        document.getElementById('cartTotal').innerText = `₱${total.toFixed(2)}`;
    }

    function updateQty(index, qty) {
        if (qty > cart[index].stock) {
            Swal.fire('Limit Reached', 'Insufficient stock.', 'warning');
            cart[index].quantity = cart[index].stock;
        } else if (qty < 1) {
            removeFromCart(index);
        } else {
            cart[index].quantity = parseInt(qty);
        }
        renderCart();
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        renderCart();
    }

    async function processCheckout() {
        const tendered = document.getElementById('tenderedAmount').value;
        if (cart.length === 0) return Swal.fire('Error', 'Cart is empty', 'error');
        if (!tendered || tendered <= 0) return Swal.fire('Error', 'Invalid amount tendered', 'error');

        const formData = new window.FormData();
        formData.append('cart_data', JSON.stringify(cart));
        formData.append('amount_tendered', tendered);
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');

        const res = await fetch('/KakaiOnesys/backend/daily_operations/process_checkout.php', {
            method: 'POST',
            body: formData
        });

        const result = await res.json();
        if (result.success) {
            Swal.fire('Success!', `Change: ₱${result.change.toFixed(2)}`, 'success').then(() => location.reload());
        } else {
            Swal.fire('Failed', result.message, 'error');
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>