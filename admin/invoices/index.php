<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin_login();

$db = Database::getConnection();

$stmt = $db->query("
    SELECT i.*, o.order_number, o.final_amount, c.name as customer_name 
    FROM invoices i 
    JOIN orders o ON i.order_id = o.id 
    JOIN customers c ON i.customer_id = c.id 
    ORDER BY i.id DESC
");
$invoices = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoices List - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Generated Tax Invoices</h3>
        <a href="../index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    <div class="card border-0 shadow-sm p-3">
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Invoice No</th>
                    <th>Order Ref</th>
                    <th>Customer Name</th>
                    <th>Amount Paid</th>
                    <th>Date Issued</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $inv): ?>
                    <tr>
                        <td><strong><?= sanitize($inv['invoice_number']) ?></strong></td>
                        <td><?= sanitize($inv['order_number']) ?></td>
                        <td><?= sanitize($inv['customer_name']) ?></td>
                        <td class="fw-bold text-success">NPR <?= number_format($inv['final_amount'], 2) ?></td>
                        <td><?= date('Y-m-d H:i', strtotime($inv['invoice_date'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>