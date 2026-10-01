<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/esewa.php';
require_once __DIR__ . '/../includes/auth.php';

require_customer_login();

$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
if (!$order_id) {
    redirect('customer/orders.php');
}

$db = Database::getConnection();
$stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ? AND payment_status = 'Pending'");
$stmt->execute([$order_id, $_SESSION['customer_id']]);
$order = $stmt->fetch();

if (!$order) {
    die("Order not found or already processed.");
}

// Generate unique transaction UUID
$transaction_uuid = $order['order_number'] . '-' . time();
$total_amount = number_format($order['final_amount'], 2, '.', '');
$product_code = ESEWA_MERCHANT_CODE;

// Save transaction record before redirecting
$stmt = $db->prepare("INSERT INTO payments (order_id, customer_id, amount, payment_method, esewa_transaction_uuid, payment_status) VALUES (?, ?, ?, 'eSewa', ?, 'Pending')");
$stmt->execute([$order['id'], $_SESSION['customer_id'], $total_amount, $transaction_uuid]);

// Build Signature (eSewa v2 HMAC Signature: total_amount,transaction_uuid,product_code)
$signature_data = "total_amount={$total_amount},transaction_uuid={$transaction_uuid},product_code={$product_code}";
$secret = ESEWA_SECRET_KEY;
$signature = base64_encode(hash_hmac('sha256', $signature_data, $secret, true));

$success_url = SITE_URL . "/payment/esewa_callback.php?status=success";
$failure_url = SITE_URL . "/payment/esewa_callback.php?status=failure";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redirecting to eSewa...</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">
    <div class="card p-4 shadow-sm text-center" style="max-width: 400px;">
        <div class="spinner-border text-success mx-auto mb-3" role="status"></div>
        <h5>Connecting to eSewa Secure Gateway</h5>
        <p class="text-muted small">Please do not refresh or navigate away from this page.</p>

        <form action="<?= ESEWA_PAYMENT_URL ?>" method="POST" id="esewaForm">
            <input type="hidden" name="amount" value="<?= $total_amount ?>" required>
            <input type="hidden" name="tax_amount" value="0" required>
            <input type="hidden" name="total_amount" value="<?= $total_amount ?>" required>
            <input type="hidden" name="transaction_uuid" value="<?= $transaction_uuid ?>" required>
            <input type="hidden" name="product_code" value="<?= $product_code ?>" required>
            <input type="hidden" name="product_service_charge" value="0" required>
            <input type="hidden" name="product_delivery_charge" value="0" required>
            <input type="hidden" name="success_url" value="<?= $success_url ?>" required>
            <input type="hidden" name="failure_url" value="<?= $failure_url ?>" required>
            <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code" required>
            <input type="hidden" name="signature" value="<?= $signature ?>" required>
        </form>
    </div>

    <script>
        document.getElementById('esewaForm').submit();
    </script>
</body>
</html>