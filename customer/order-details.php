<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_customer_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$db = Database::getConnection();
$stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ?");
$stmt->execute([$id, $_SESSION['customer_id']]);
$order = $stmt->fetch();

if (!$order) {
    redirect('customer/orders.php');
}

$car = json_decode($order['car_snapshot'], true);
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="card border-0 shadow-sm p-4">
        <h4 class="fw-bold mb-3">Order #<?= sanitize($order['order_number']) ?></h4>
        <div class="row">
            <div class="col-md-6">
                <h6>Vehicle Details</h6>
                <p><strong>Name:</strong> <?= sanitize($car['brand'] . ' ' . $car['model']) ?></p>
                <p><strong>Year:</strong> <?= sanitize($car['year']) ?></p>
                <p><strong>Fuel:</strong> <?= sanitize($car['fuel_type']) ?></p>
            </div>
            <div class="col-md-6">
                <h6>Payment Info</h6>
                <p><strong>Total Amount:</strong> NPR <?= number_format($order['final_amount'], 2) ?></p>
                <p><strong>Status:</strong> <?= sanitize($order['payment_status']) ?></p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>