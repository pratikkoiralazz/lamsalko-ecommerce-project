<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin_login();

$db = Database::getConnection();

$stmt = $db->query("
    SELECT p.*, o.order_number, c.name as customer_name, c.email as customer_email 
    FROM payments p 
    JOIN orders o ON p.order_id = o.id 
    JOIN customers c ON p.customer_id = c.id 
    ORDER BY p.id DESC
");
$payments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Records - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Payment Transaction Records</h3>
        <a href="../index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    <div class="card border-0 shadow-sm p-3">
        <table class="table table-bordered table-striped align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Payment ID</th>
                    <th>Order Ref</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>eSewa Ref ID</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $pay): ?>
                    <tr>
                        <td>#<?= $pay['id'] ?></td>
                        <td><strong><?= sanitize($pay['order_number']) ?></strong></td>
                        <td><?= sanitize($pay['customer_name']) ?><br><small class="text-muted"><?= sanitize($pay['customer_email']) ?></small></td>
                        <td class="fw-bold text-success">NPR <?= number_format($pay['amount'], 2) ?></td>
                        <td><span class="badge bg-primary"><?= sanitize($pay['payment_method']) ?></span></td>
                        <td><code><?= sanitize($pay['esewa_ref_id'] ?? 'N/A') ?></code></td>
                        <td>
                            <span class="badge bg-<?= $pay['payment_status'] === 'Success' ? 'success' : ($pay['payment_status'] === 'Pending' ? 'warning' : 'danger') ?>">
                                <?= $pay['payment_status'] ?>
                            </span>
                        </td>
                        <td><?= date('Y-m-d H:i', strtotime($pay['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>