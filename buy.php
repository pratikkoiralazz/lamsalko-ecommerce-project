<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth.php';

require_customer_login();

$car_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$car_id) {
    redirect('cars.php');
}

$db = Database::getConnection();
$stmt = $db->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->execute([$car_id]);
$car = $stmt->fetch();

if (!$car || $car['availability_status'] !== 'Available') {
    $_SESSION['flash_error'] = "Vehicle is no longer available for purchase.";
    redirect('cars.php');
}

$stmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$_SESSION['customer_id']]);
$customer = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::checkToken($_POST['csrf_token'] ?? '')) {
        die("Security token validation failed.");
    }

    try {
        $db->beginTransaction();

        // Double Check Vehicle Availability via Lock
        $stmt = $db->prepare("SELECT availability_status FROM cars WHERE id = ? FOR UPDATE");
        $stmt->execute([$car_id]);
        $current_status = $stmt->fetchColumn();

        if ($current_status !== 'Available') {
            $db->rollBack();
            $_SESSION['flash_error'] = "Sorry, another customer just locked or purchased this car.";
            redirect('cars.php');
        }

        $order_number = "ORD-" . date('Ymd') . "-" . rand(1000, 9999);
        $price = $car['price'];
        $discount = $car['discount_price'] > 0 ? ($price - $car['discount_price']) : 0;
        $final_amount = $car['discount_price'] > 0 ? $car['discount_price'] : $price;
        $snapshot = json_encode($car);

        $stmt = $db->prepare("INSERT INTO orders (order_number, customer_id, car_id, car_snapshot, price, discount, final_amount, payment_status, order_status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', 'Pending Payment')");
        $stmt->execute([$order_number, $customer['id'], $car['id'], $snapshot, $price, $discount, $final_amount]);
        $order_id = $db->lastInsertId();

        $db->commit();
        redirect("payment/esewa_payment.php?order_id=" . $order_id);

    } catch (Exception $e) {
        $db->rollBack();
        error_log("Order Creation Error: " . $e->getMessage());
        $error = "Transaction failed due to an unexpected issue.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - <?= sanitize($car['brand'] . ' ' . $car['model']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white fw-bold">Order Summary & Confirmation</div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <img src="<?= UPLOAD_URL . sanitize($car['main_image']) ?>" class="img-fluid rounded" alt="Car Image">
                        </div>
                        <div class="col-md-8">
                            <h5><?= sanitize($car['brand'] . ' ' . $car['model'] . ' (' . $car['year'] . ')') ?></h5>
                            <p class="text-muted mb-1">Variant: <?= sanitize($car['variant']) ?></p>
                            <p class="text-muted mb-1">Color: <?= sanitize($car['color']) ?> | Engine: <?= sanitize($car['engine']) ?></p>
                            <h4 class="text-success mt-2">
                                NPR <?= number_format($car['discount_price'] > 0 ? $car['discount_price'] : $car['price'], 2) ?>
                            </h4>
                        </div>
                    </div>
                    <hr>
                    <h6>Customer Buyer Details</h6>
                    <p class="mb-1"><strong>Name:</strong> <?= sanitize($customer['name']) ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?= sanitize($customer['email']) ?></p>
                    <p class="mb-1"><strong>Phone:</strong> <?= sanitize($customer['phone']) ?></p>
                    <p class="mb-3"><strong>Delivery Address:</strong> <?= sanitize($customer['address'] . ', ' . $customer['city']) ?></p>

                    <form method="POST">
                        <?php CSRF::insertField(); ?>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">Proceed to eSewa Payment</button>
                            <a href="car-details.php?id=<?= $car['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>