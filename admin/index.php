<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/admin-sidebar.php';

$db = Database::getConnection();

// System Metrics
$total_cars = $db->query("SELECT COUNT(*) FROM cars")->fetchColumn();
$available_cars = $db->query("SELECT COUNT(*) FROM cars WHERE availability_status = 'Available'")->fetchColumn();
$sold_cars = $db->query("SELECT COUNT(*) FROM cars WHERE availability_status = 'Sold'")->fetchColumn();
$total_customers = $db->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$total_orders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pending_test_drives = $db->query("SELECT COUNT(*) FROM test_drive_bookings WHERE status = 'Pending'")->fetchColumn();
$total_revenue = $db->query("SELECT SUM(final_amount) FROM orders WHERE payment_status = 'Success'")->fetchColumn() ?? 0;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">Dashboard Executive Overview</h3>
    <a href="cars/add.php" class="btn btn-primary btn-lg"><i class="bi bi-plus-circle me-1"></i> Add New Vehicle</a>
</div>

<!-- Metrics Overview -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3 shadow-sm border-0 bg-white border-start border-4 border-success">
            <span class="text-muted small fw-bold">TOTAL REVENUE</span>
            <h3 class="text-success fw-bold mt-2 mb-0">NPR <?= number_format($total_revenue, 2) ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 shadow-sm border-0 bg-white border-start border-4 border-primary">
            <span class="text-muted small fw-bold">TOTAL FLEET STOCK</span>
            <h3 class="fw-bold mt-2 mb-0"><?= $total_cars ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 shadow-sm border-0 bg-white border-start border-4 border-info">
            <span class="text-muted small fw-bold">AVAILABLE STOCK</span>
            <h3 class="text-info fw-bold mt-2 mb-0"><?= $available_cars ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 shadow-sm border-0 bg-white border-start border-4 border-danger">
            <span class="text-muted small fw-bold">VEHICLES SOLD</span>
            <h3 class="text-danger fw-bold mt-2 mb-0"><?= $sold_cars ?></h3>
        </div>
    </div>
</div>

<!-- Quick Link Modules Grid -->
<h5 class="fw-bold mb-3">Management Portal Folders</h5>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card p-3 border-0 shadow-sm">
            <div class="d-flex align-items-center">
                <i class="bi bi-car-front fs-1 text-primary me-3"></i>
                <div>
                    <h6 class="fw-bold mb-1">Cars Inventory</h6>
                    <small class="text-muted">Manage listings, set featured cars & uploads</small>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <a href="cars/index.php" class="btn btn-sm btn-outline-primary">View Inventory</a>
                <a href="cars/add.php" class="btn btn-sm btn-primary">+ Add Car</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 border-0 shadow-sm">
            <div class="d-flex align-items-center">
                <i class="bi bi-cart-check fs-1 text-success me-3"></i>
                <div>
                    <h6 class="fw-bold mb-1">Orders Management</h6>
                    <small class="text-muted"><?= $total_orders ?> Total purchase orders logged</small>
                </div>
            </div>
            <a href="orders/index.php" class="btn btn-sm btn-outline-success mt-3">View Orders</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 border-0 shadow-sm">
            <div class="d-flex align-items-center">
                <i class="bi bi-calendar-event fs-1 text-warning me-3"></i>
                <div>
                    <h6 class="fw-bold mb-1">Test Drive Bookings</h6>
                    <small class="text-muted"><?= $pending_test_drives ?> Pending customer requests</small>
                </div>
            </div>
            <a href="bookings/index.php" class="btn btn-sm btn-outline-warning mt-3">Manage Requests</a>
        </div>
    </div>
</div>

    </div> <!-- Close flex-grow-1 -->
</div> <!-- Close d-flex -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>