<?php
// frontend/includes/sidebar.php
$userRole = $_SESSION['role_name'] ?? '';
?>

<nav id="sidebar" class="bg-dark text-white shadow-sm vh-100">

    <!-- Logo -->
    <div class="sidebar-header p-3 text-center border-bottom border-secondary">
        <h4 class="mb-0 text-uppercase fw-bold">
            <i class="fa-solid fa-cookie-bite text-warning"></i> Kakai's
        </h4>
        <small class="text-muted">Wholesale & Retail</small>
    </div>

    <!-- User Info -->
    <div class="p-3 user-info border-bottom border-secondary text-center">
        <i class="fa-solid fa-circle-user fa-2x mb-2 text-light"></i>
        <h6 class="mb-0">
            <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>
        </h6>
        <span class="badge bg-warning text-dark">
            <?php echo htmlspecialchars($userRole); ?>
        </span>
    </div>

    <div class="accordion accordion-flush" id="sidebarAccordion">

        <!-- Dashboard -->
        <a href="/KakaiOnesys/frontend/dashboard/admin_dashboard.php"
            class="sidebar-link p-3 d-block text-white text-decoration-none border-bottom border-secondary">
            <i class="fa-solid fa-chart-line me-2"></i> Dashboard
        </a>

        <!-- Daily Operations -->
        <?php if ($userRole === 'Admin' || $userRole === 'Cashier' || hasPermission('can_view_pos')): ?>
            <div class="accordion-item bg-dark border-0 border-bottom border-secondary">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed bg-dark text-white shadow-none"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseDaily">
                        <i class="fa-solid fa-cash-register me-2 text-info"></i> Daily Operations
                    </button>
                </h2>

                <div id="collapseDaily" class="accordion-collapse collapse" data-bs-parent="#sidebarAccordion">
                    <div class="accordion-body p-0">

                        <a href="/KakaiOnesys/frontend/daily_operations/pos.php"
                            class="sidebar-sublink">
                            <i class="fa-solid fa-cart-shopping me-2"></i> POS Checkout
                        </a>

                        <a href="/KakaiOnesys/frontend/daily_operations/damage_entry.php"
                            class="sidebar-sublink">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i> Damage/Loss Entry
                        </a>

                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Inventory -->
        <?php if ($userRole === 'Admin' || $userRole === 'Stockman' || hasPermission('can_manage_inventory')): ?>
            <div class="accordion-item bg-dark border-0 border-bottom border-secondary">

                <h2 class="accordion-header">
                    <button class="accordion-button collapsed bg-dark text-white shadow-none"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseInventory">
                        <i class="fa-solid fa-boxes-stacked me-2 text-success"></i>
                        Inventory & Logistics
                    </button>
                </h2>

                <div id="collapseInventory" class="accordion-collapse collapse"
                    data-bs-parent="#sidebarAccordion">

                    <div class="accordion-body p-0">

                        <a href="/KakaiOnesys/frontend/inventory_logistics/receive_shipment.php"
                            class="sidebar-sublink">
                            <i class="fa-solid fa-truck-ramp-box me-2"></i> Receive Shipment
                        </a>

                        <a href="/KakaiOnesys/frontend/inventory_logistics/bulk_breakdown.php"
                            class="sidebar-sublink">
                            <i class="fa-solid fa-box-open me-2"></i> Explode Bulk
                        </a>

                        <a href="/KakaiOnesys/frontend/inventory_logistics/stock_transfer.php"
                            class="sidebar-sublink">
                            <i class="fa-solid fa-people-carry-box me-2"></i> Stock Transfer
                        </a>

                        <a href="/KakaiOnesys/frontend/inventory_logistics/stock_movements.php"
                            class="sidebar-sublink">
                            <i class="fa-solid fa-clock-rotate-left me-2"></i> Movements
                        </a>

                    </div>
                </div>

            </div>
        <?php endif; ?>

        <!-- Business Intelligence -->
        <?php if ($userRole === 'Admin'): ?>
            <div class="accordion-item bg-dark border-0 border-bottom border-secondary">

                <h2 class="accordion-header">
                    <button class="accordion-button collapsed bg-dark text-white shadow-none"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseBI">
                        <i class="fa-solid fa-magnifying-glass-chart me-2 text-warning"></i>
                        Business Intelligence
                    </button>
                </h2>

                <div id="collapseBI" class="accordion-collapse collapse"
                    data-bs-parent="#sidebarAccordion">

                    <div class="accordion-body p-0">

                        <a href="/KakaiOnesys/frontend/business_intelligence/profitability_reports.php"
                            class="sidebar-sublink">
                            <i class="fa-solid fa-file-invoice-dollar me-2"></i> Profitability
                        </a>

                        <a href="/KakaiOnesys/frontend/business_intelligence/inventory_forecast.php"
                            class="sidebar-sublink">
                            <i class="fa-solid fa-arrow-trend-up me-2"></i> Forecast
                        </a>

                    </div>
                </div>

            </div>
        <?php endif; ?>

        <!-- Logout -->
        <a href="/KakaiOnesys/backend/auth/logout.php"
            class="sidebar-link p-3 d-block text-danger text-decoration-none border-top border-secondary">
            <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
        </a>

    </div>

</nav>

<!-- Page Content -->
<div id="content" class="w-100 bg-light">
    <div class="container-fluid p-4">