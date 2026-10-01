<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

require_customer_login();

$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
if ($order_id) {
    redirect("payment/esewa_payment.php?order_id=" . $order_id);
} else {
    redirect("customer/orders.php");
}