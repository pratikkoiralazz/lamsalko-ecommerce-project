<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/esewa.php';

$status = $_GET['status'] ?? 'failure';
$encoded_data = $_GET['data'] ?? null;

if ($status !== 'success' || !$encoded_data) {
    $_SESSION['flash_error'] = "Payment process was canceled or failed.";
    redirect('customer/orders.php');
}

// Decode Base64 response from eSewa
$json_data = base64_decode($encoded_data);
$response = json_decode($json_data, true);

if (!$response || !isset($response['transaction_uuid'])) {
    die("Invalid gateway response.");
}

$transaction_uuid = $response['transaction_uuid'];
$total_amount = $response['total_amount'];
$ref_id = $response['ref_id'] ?? null;

// Server-Side Verification Execution
require_once __DIR__ . '/esewa_verify.php';
$is_verified = verify_esewa_transaction($transaction_uuid, $total_amount);

$db = Database::getConnection();

if ($is_verified) {
    try {
        $db->beginTransaction();

        // Lock payment record
        $stmt = $db->prepare("SELECT * FROM payments WHERE esewa_transaction_uuid = ? FOR UPDATE");
        $stmt->execute([$transaction_uuid]);
        $payment = $stmt->fetch();

        if ($payment && $payment['payment_status'] === 'Pending') {
            // Update Payment Record
            $stmt = $db->prepare("UPDATE payments SET payment_status = 'Success', esewa_ref_id = ?, gateway_response = ? WHERE id = ?");
            $stmt->execute([$ref_id, $json_data, $payment['id']]);

            // Update Order Record
            $stmt = $db->prepare("UPDATE orders SET payment_status = 'Success', order_status = 'Paid' WHERE id = ?");
            $stmt->execute([$payment['order_id']]);

            // Get Car ID from order
            $stmt = $db->prepare("SELECT car_id FROM orders WHERE id = ?");
            $stmt->execute([$payment['order_id']]);
            $order = $stmt->fetch();

            // Mark Vehicle as Sold
            $stmt = $db->prepare("UPDATE cars SET availability_status = 'Sold' WHERE id = ?");
            $stmt->execute([$order['car_id']]);

            // Generate Invoice Record
            $inv_num = "INV-" . strtoupper(bin2hex(random_bytes(4)));
            $stmt = $db->prepare("INSERT INTO invoices (invoice_number, order_id, customer_id) VALUES (?, ?, ?)");
            $stmt->execute([$inv_num, $payment['order_id'], $payment['customer_id']]);

            $db->commit();
            $_SESSION['flash_success'] = "Payment verified successfully! Vehicle marked as SOLD.";
            redirect("customer/invoices.php");
        } else {
            $db->rollBack();
            redirect("customer/orders.php");
        }
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Transaction Verification Error: " . $e->getMessage());
        die("An unexpected error occurred during database updating.");
    }
} else {
    $stmt = $db->prepare("UPDATE payments SET payment_status = 'Failed', gateway_response = ? WHERE esewa_transaction_uuid = ?");
    $stmt->execute([$json_data, $transaction_uuid]);
    $_SESSION['flash_error'] = "Server-side payment verification failed!";
    redirect("customer/orders.php");
}