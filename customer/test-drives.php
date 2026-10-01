<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_customer_login();

$db = Database::getConnection();
$stmt = $db->prepare("
    SELECT t.*, c.brand, c.model 
    FROM test_drive_bookings t 
    JOIN cars c ON t.car_id = c.id 
    WHERE t.customer_id = ? 
    ORDER BY t.id DESC
");
$stmt->execute([$_SESSION['customer_id']]);
$bookings = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <h3 class="fw-bold mb-4">My Test Drive Bookings</h3>
    <table class="table table-bordered bg-white shadow-sm">
        <thead class="table-dark">
            <tr>
                <th>Vehicle</th>
                <th>Preferred Date</th>
                <th>Time</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><?= sanitize($b['brand'] . ' ' . $b['model']) ?></td>
                    <td><?= sanitize($b['preferred_date']) ?></td>
                    <td><?= sanitize($b['preferred_time']) ?></td>
                    <td><span class="badge bg-info"><?= sanitize($b['status']) ?></span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>