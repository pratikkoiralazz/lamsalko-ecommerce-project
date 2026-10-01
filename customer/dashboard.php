<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_customer_login();

$db = Database::getConnection();

// Customer Recent Orders
$stmt = $db->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY id DESC LIMIT 5");
$stmt->execute([$_SESSION['customer_id']]);
$orders = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-3">
            <div class="list-group shadow-sm">
                <a href="dashboard.php" class="list-group-item list-group-item-action active">Dashboard</a>
                <a href="orders.php" class="list-group-item list-group-item-action">My Orders</a>
                <a href="invoices.php" class="list-group-item list-group-item-action">Invoices</a>
                <a href="test-drives.php" class="list-group-item list-group-item-action">Test Drive Bookings</a>
                <a href="profile.php" class="list-group-item list-group-item-action">Profile Settings</a>
            </div>
        </div>
        <div class="col-md-9">
            <h3 class="fw-bold mb-4">Welcome back, <?= sanitize($_SESSION['customer_name']) ?>!</h3>
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h5 class="fw-bold">Recent Orders</h5>
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Order No</th>
                            <th>Amount</th>
                            <th>Order Status</th>
                            <th>Payment Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $ord): ?>
                            <tr>
                                <td><?= sanitize($ord['order_number']) ?></td>
                                <td>NPR <?= number_format($ord['final_amount'], 2) ?></td>
                                <td><span class="badge bg-info"><?= sanitize($ord['order_status']) ?></span></td>
                                <td><span class="badge bg-<?= $ord['payment_status'] === 'Success' ? 'success' : 'warning' ?>"><?= sanitize($ord['payment_status']) ?></span></td>
                                <td><a href="order-details.php?id=<?= $ord['id'] ?>" class="btn btn-sm btn-outline-dark">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>