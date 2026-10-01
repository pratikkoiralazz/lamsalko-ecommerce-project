<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_customer_login();

$db = Database::getConnection();
$stmt = $db->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY id DESC");
$stmt->execute([$_SESSION['customer_id']]);
$orders = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <h3 class="fw-bold mb-4">My Orders</h3>
    <table class="table table-bordered bg-white shadow-sm align-middle">
        <thead class="table-dark">
            <tr>
                <th>Order Number</th>
                <th>Date</th>
                <th>Total Price</th>
                <th>Payment Status</th>
                <th>Order Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $ord): ?>
                <tr>
                    <td><?= sanitize($ord['order_number']) ?></td>
                    <td><?= date('Y-m-d', strtotime($ord['created_at'])) ?></td>
                    <td>NPR <?= number_format($ord['final_amount'], 2) ?></td>
                    <td><span class="badge bg-<?= $ord['payment_status'] === 'Success' ? 'success' : 'warning' ?>"><?= sanitize($ord['payment_status']) ?></span></td>
                    <td><span class="badge bg-secondary"><?= sanitize($ord['order_status']) ?></span></td>
                    <td>
                        <?php if ($ord['payment_status'] === 'Pending'): ?>
                            <a href="<?= SITE_URL ?>/payment/esewa_payment.php?order_id=<?= $ord['id'] ?>" class="btn btn-sm btn-success">Pay with eSewa</a>
                        <?php else: ?>
                            <a href="order-details.php?id=<?= $ord['id'] ?>" class="btn btn-sm btn-outline-dark">View Order</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>