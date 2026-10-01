<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_customer_login();

$db = Database::getConnection();
$stmt = $db->prepare("
    SELECT i.*, o.order_number, o.car_snapshot, o.final_amount, o.created_at as order_date, p.esewa_ref_id
    FROM invoices i
    JOIN orders o ON i.order_id = o.id
    LEFT JOIN payments p ON p.order_id = o.id AND p.payment_status = 'Success'
    WHERE i.customer_id = ?
    ORDER BY i.id DESC
");
$stmt->execute([$_SESSION['customer_id']]);
$invoices = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Invoices - Apex Motors</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h3 class="mb-4">Tax Invoices</h3>
    <?php if (empty($invoices)): ?>
        <div class="alert alert-info">No paid invoices found.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($invoices as $inv): 
                $car = json_decode($inv['car_snapshot'], true);
            ?>
            <div class="col-md-12 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4" id="printable-<?= $inv['id'] ?>">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="fw-bold text-primary"><?= SITE_NAME ?></h4>
                                <p class="text-muted small mb-0">Kathmandu, Nepal | Phone: +977-1-4433221</p>
                            </div>
                            <div class="text-end">
                                <h5 class="mb-0">INVOICE</h5>
                                <span class="badge bg-success">PAID</span>
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3 small">
                            <div class="col-6">
                                <strong>Invoice No:</strong> <?= sanitize($inv['invoice_number']) ?><br>
                                <strong>Order Ref:</strong> <?= sanitize($inv['order_number']) ?><br>
                                <strong>Date:</strong> <?= date('M d, Y', strtotime($inv['invoice_date'])) ?>
                            </div>
                            <div class="col-6 text-end">
                                <strong>eSewa Ref ID:</strong> <?= sanitize($inv['esewa_ref_id'] ?? 'N/A') ?><br>
                                <strong>Payment Method:</strong> eSewa Digital Wallet
                            </div>
                        </div>
                        <table class="table table-bordered align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Description</th>
                                    <th>Specs</th>
                                    <th class="text-end">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <strong><?= sanitize($car['brand'] . ' ' . $car['model']) ?></strong><br>
                                        <span class="text-muted small">Reg Info: <?= sanitize($car['registration_info']) ?></span>
                                    </td>
                                    <td class="small">
                                        Engine: <?= sanitize($car['engine']) ?><br>
                                        Fuel: <?= sanitize($car['fuel_type']) ?>
                                    </td>
                                    <td class="text-end fw-bold">NPR <?= number_format($inv['final_amount'], 2) ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-end align-items-center gap-2 mt-3 d-print-none">
                            <button onclick="window.print()" class="btn btn-outline-primary btn-sm">Print Invoice</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>