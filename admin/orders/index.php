<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin_login();

$db = Database::getConnection();
$orders = $db->query("SELECT o.*, c.name as customer_name FROM orders o JOIN customers c ON o.customer_id = c.id ORDER BY o.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Management - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex justify-content-between mb-4">
        <h3>Customer Orders</h3>
        <a href="../index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    <table class="table table-bordered bg-white shadow-sm">
        <thead class="table-dark">
            <tr>
                <th>Order No</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Payment Status</th>
                <th>Order Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td><?= sanitize($o['order_number']) ?></td>
                    <td><?= sanitize($o['customer_name']) ?></td>
                    <td>NPR <?= number_format($o['final_amount'], 2) ?></td>
                    <td><?= sanitize($o['payment_status']) ?></td>
                    <td><?= sanitize($o['order_status']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>