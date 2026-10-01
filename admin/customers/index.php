<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

require_admin_login();

$db = Database::getConnection();

// Toggle Customer Account Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    if (!CSRF::checkToken($_POST['csrf_token'] ?? '')) {
        die("Security token validation failed.");
    }
    $customer_id = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
    $current_status = sanitize($_POST['current_status'] ?? '');
    $new_status = ($current_status === 'Active') ? 'Inactive' : 'Active';

    $stmt = $db->prepare("UPDATE customers SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $customer_id]);
    
    log_audit_action($_SESSION['admin_id'], 'CUSTOMER_STATUS_TOGGLE', "Toggled status of customer ID {$customer_id} to {$new_status}");
    redirect('admin/customers/index.php');
}

$customers = $db->query("SELECT c.*, (SELECT COUNT(*) FROM orders WHERE customer_id = c.id) as total_orders FROM customers c ORDER BY c.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Management - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Customer Management</h3>
        <a href="../index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    <div class="card border-0 shadow-sm p-3">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>Orders</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $cust): ?>
                    <tr>
                        <td><?= $cust['id'] ?></td>
                        <td><strong><?= sanitize($cust['name']) ?></strong></td>
                        <td><?= sanitize($cust['email']) ?></td>
                        <td><?= sanitize($cust['phone']) ?></td>
                        <td><?= sanitize($cust['city']) ?></td>
                        <td><span class="badge bg-secondary"><?= $cust['total_orders'] ?></span></td>
                        <td>
                            <span class="badge bg-<?= $cust['status'] === 'Active' ? 'success' : 'danger' ?>">
                                <?= $cust['status'] ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" class="d-inline">
                                <?php CSRF::insertField(); ?>
                                <input type="hidden" name="customer_id" value="<?= $cust['id'] ?>">
                                <input type="hidden" name="current_status" value="<?= $cust['status'] ?>">
                                <button type="submit" name="toggle_status" class="btn btn-sm btn-outline-<?= $cust['status'] === 'Active' ? 'warning' : 'success' ?>">
                                    <?= $cust['status'] === 'Active' ? 'Deactivate' : 'Activate' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>