<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin_login();

$db = Database::getConnection();

// Export CSV handler
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sales_report_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Order Number', 'Customer', 'Final Amount', 'Payment Status', 'Date']);

    $stmt = $db->query("SELECT o.order_number, c.name, o.final_amount, o.payment_status, o.created_at FROM orders o JOIN customers c ON o.customer_id = c.id WHERE o.payment_status = 'Success'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

$sales_summary = $db->query("
    SELECT 
        COUNT(*) as total_sales_count, 
        SUM(final_amount) as total_revenue, 
        AVG(final_amount) as avg_sale_value 
    FROM orders 
    WHERE payment_status = 'Success'
")->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales & Analytics Reports - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Financial & Sales Analytics</h3>
        <div>
            <a href="index.php?export=csv" class="btn btn-success me-2">Export CSV Report</a>
            <a href="../index.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
    
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 bg-white">
                <span class="text-muted">Total Gross Revenue</span>
                <h3 class="text-success fw-bold mt-2">NPR <?= number_format($sales_summary['total_revenue'] ?? 0, 2) ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 bg-white">
                <span class="text-muted">Successful Orders</span>
                <h3 class="text-primary fw-bold mt-2"><?= $sales_summary['total_sales_count'] ?? 0 ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 bg-white">
                <span class="text-muted">Average Order Value</span>
                <h3 class="text-info fw-bold mt-2">NPR <?= number_format($sales_summary['avg_sale_value'] ?? 0, 2) ?></h3>
            </div>
        </div>
    </div>
</div>
</body>
</html>