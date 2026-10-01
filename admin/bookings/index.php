<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

require_admin_login();

$db = Database::getConnection();

// Update Booking Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!CSRF::checkToken($_POST['csrf_token'] ?? '')) {
        die("CSRF Security Check Failed");
    }
    $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    $status = sanitize($_POST['status'] ?? 'Pending');

    $stmt = $db->prepare("UPDATE test_drive_bookings SET status = ? WHERE id = ?");
    $stmt->execute([$status, $booking_id]);

    log_audit_action($_SESSION['admin_id'], 'BOOKING_UPDATE', "Updated booking ID {$booking_id} status to {$status}");
    redirect('admin/bookings/index.php');
}

$stmt = $db->query("
    SELECT t.*, c.brand, c.model, cust.name as customer_name 
    FROM test_drive_bookings t 
    JOIN cars c ON t.car_id = c.id 
    JOIN customers cust ON t.customer_id = cust.id 
    ORDER BY t.id DESC
");
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Drive Bookings - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Test Drive Bookings</h3>
        <a href="../index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    <div class="card border-0 shadow-sm p-3">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Vehicle</th>
                    <th>Date & Time</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td>#<?= $b['id'] ?></td>
                        <td><?= sanitize($b['customer_name']) ?></td>
                        <td><strong><?= sanitize($b['brand'] . ' ' . $b['model']) ?></strong></td>
                        <td><?= sanitize($b['preferred_date']) ?> at <?= sanitize($b['preferred_time']) ?></td>
                        <td><?= sanitize($b['phone']) ?></td>
                        <td><span class="badge bg-info"><?= sanitize($b['status']) ?></span></td>
                        <td>
                            <form method="POST" class="d-flex gap-1">
                                <?php CSRF::insertField(); ?>
                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                <select name="status" class="form-select form-select-sm" style="width: 130px;">
                                    <option value="Pending" <?= $b['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Approved" <?= $b['status'] === 'Approved' ? 'selected' : '' ?>>Approve</option>
                                    <option value="Rejected" <?= $b['status'] === 'Rejected' ? 'selected' : '' ?>>Reject</option>
                                    <option value="Completed" <?= $b['status'] === 'Completed' ? 'selected' : '' ?>>Complete</option>
                                    <option value="Cancelled" <?= $b['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancel</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-sm btn-primary">Save</button>
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