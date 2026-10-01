<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_customer_login();

$db = Database::getConnection();
$stmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$_SESSION['customer_id']]);
$customer = $stmt->fetch();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="card border-0 shadow-sm p-4 col-md-6 mx-auto">
        <h4 class="fw-bold mb-3">Profile Details</h4>
        <p><strong>Name:</strong> <?= sanitize($customer['name']) ?></p>
        <p><strong>Email:</strong> <?= sanitize($customer['email']) ?></p>
        <p><strong>Phone:</strong> <?= sanitize($customer['phone']) ?></p>
        <p><strong>City:</strong> <?= sanitize($customer['city']) ?></p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>